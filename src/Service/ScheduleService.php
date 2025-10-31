<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

/**
 * 排课服务实现
 *
 * 提供教室排课管理的核心业务功能实现
 */
#[WithMonologChannel(channel: 'train_classroom')]
class ScheduleService implements ScheduleServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly ClassroomRepository $classroomRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createSchedule(
        Classroom $classroom,
        int $courseId,
        ScheduleType $type,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        array $options = [],
    ): ClassroomSchedule {
        // 验证时间参数
        if ($startTime >= $endTime) {
            throw new InvalidArgumentException('开始时间必须早于结束时间');
        }

        // 检测时间冲突
        $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);
        if ([] !== $conflicts) {
            throw new InvalidArgumentException('排课时间冲突，已存在' . \count($conflicts) . '个冲突的排课');
        }

        // 创建排课记录
        $schedule = new ClassroomSchedule();
        $schedule->setClassroom($classroom);
        $schedule->setTeacherId((string) $courseId);
        $schedule->setScheduleType($type);
        $schedule->setStartTime(\DateTimeImmutable::createFromInterface($startTime));
        $schedule->setEndTime(\DateTimeImmutable::createFromInterface($endTime));
        $schedule->setScheduleStatus(ScheduleStatus::SCHEDULED);

        // 设置可选参数
        if (isset($options['course_content']) && is_string($options['course_content'])) {
            $schedule->setCourseContent($options['course_content']);
        }
        if (isset($options['expected_students']) && is_numeric($options['expected_students'])) {
            $schedule->setExpectedStudents((int) $options['expected_students']);
        }
        if (isset($options['remark']) && is_string($options['remark'])) {
            $schedule->setRemark($options['remark']);
        }

        $this->entityManager->persist($schedule);
        $this->entityManager->flush();

        $this->logger->info('排课创建成功', [
            'schedule_id' => $schedule->getId(),
            'classroom_id' => $classroom->getId(),
            'course_id' => $courseId,
            'type' => $type->value,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
        ]);

        return $schedule;
    }

    /**
     * @return array<int, ClassroomSchedule>
     */
    public function detectScheduleConflicts(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeScheduleId = null,
    ): array {
        $conflicts = $this->scheduleRepository->findConflictingSchedules(
            $classroom,
            \DateTimeImmutable::createFromInterface($startTime),
            \DateTimeImmutable::createFromInterface($endTime),
            null !== $excludeScheduleId ? (string) $excludeScheduleId : null
        );

        return array_values($conflicts);
    }

    public function updateScheduleStatus(
        ClassroomSchedule $schedule,
        ScheduleStatus $status,
        ?string $reason = null,
    ): ClassroomSchedule {
        $oldStatus = $schedule->getScheduleStatus();
        $schedule->setScheduleStatus($status);

        if (null !== $reason) {
            $currentRemark = $schedule->getRemark();
            $newRemark = (null !== $currentRemark && '' !== $currentRemark)
                ? $currentRemark . "\n状态变更：{$oldStatus->getDescription()} -> {$status->getDescription()}，原因：{$reason}"
                : "状态变更：{$oldStatus->getDescription()} -> {$status->getDescription()}，原因：{$reason}";
            $schedule->setRemark($newRemark);
        }

        $this->entityManager->flush();

        $this->logger->info('排课状态更新', [
            'schedule_id' => $schedule->getId(),
            'old_status' => $oldStatus->value,
            'new_status' => $status->value,
            'reason' => $reason,
        ]);

        return $schedule;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClassroomUtilizationRate(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
    ): array {
        return $this->scheduleRepository->getClassroomUtilizationRate(
            $classroom,
            $startDate,
            $endDate
        );
    }

    /**
     * @param array<string, mixed> $requiredFeatures
     * @return array<int, array<string, mixed>>
     */
    public function findAvailableClassrooms(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $minCapacity = null,
        array $requiredFeatures = [],
    ): array {
        $allClassrooms = $this->classroomRepository->findAll();
        $availableClassrooms = $this->filterAvailableClassrooms(
            $allClassrooms,
            $startTime,
            $endTime,
            $minCapacity,
            $requiredFeatures
        );

        return $this->sortClassroomsByCapacity($availableClassrooms);
    }

    /**
     * @param array<int, Classroom> $classrooms
     * @param array<string, mixed> $requiredFeatures
     * @return array<int, array<string, mixed>>
     */
    private function filterAvailableClassrooms(
        array $classrooms,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $minCapacity,
        array $requiredFeatures,
    ): array {
        $availableClassrooms = [];

        foreach ($classrooms as $classroom) {
            if ($this->isClassroomAvailable($classroom, $startTime, $endTime, $minCapacity, $requiredFeatures)) {
                $availableClassrooms[] = $this->buildClassroomInfo($classroom);
            }
        }

        return $availableClassrooms;
    }

    /**
     * @param array<string, mixed> $requiredFeatures
     */
    private function isClassroomAvailable(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $minCapacity,
        array $requiredFeatures,
    ): bool {
        return $this->isClassroomSuitableForRequirements($classroom, $minCapacity, $requiredFeatures)
            && $this->isClassroomAvailableForTime($classroom, $startTime, $endTime);
    }

    /**
     * @param array<string, mixed> $requiredFeatures
     */
    private function isClassroomSuitableForRequirements(
        Classroom $classroom,
        ?int $minCapacity,
        array $requiredFeatures,
    ): bool {
        if (!$this->meetsCapacityRequirement($classroom, $minCapacity)) {
            return false;
        }

        return $this->hasRequiredFeatures($classroom, $requiredFeatures);
    }

    private function meetsCapacityRequirement(Classroom $classroom, ?int $minCapacity): bool
    {
        return null === $minCapacity || $classroom->getCapacity() >= $minCapacity;
    }

    /**
     * @param array<string, mixed> $requiredFeatures
     */
    private function hasRequiredFeatures(Classroom $classroom, array $requiredFeatures): bool
    {
        if ([] === $requiredFeatures) {
            return true;
        }

        $classroomFeatures = $classroom->getDevices() ?? [];
        foreach ($requiredFeatures as $feature) {
            if (!\in_array($feature, $classroomFeatures, true)) {
                return false;
            }
        }

        return true;
    }

    private function isClassroomAvailableForTime(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
    ): bool {
        $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);

        return [] === $conflicts;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildClassroomInfo(Classroom $classroom): array
    {
        return [
            'classroom' => $classroom,
            'capacity' => $classroom->getCapacity(),
            'devices' => $classroom->getDevices(),
            'location' => $classroom->getLocation(),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $classrooms
     * @return array<int, array<string, mixed>>
     */
    private function sortClassroomsByCapacity(array $classrooms): array
    {
        usort($classrooms, fn ($a, $b) => $b['capacity'] <=> $a['capacity']);

        return $classrooms;
    }

    /**
     * @param array<int, array<string, mixed>> $scheduleData
     * @return array<string, mixed>
     */
    public function batchCreateSchedules(array $scheduleData, bool $skipConflicts = false): array
    {
        $results = $this->initializeBatchResults();

        foreach ($scheduleData as $index => $data) {
            $results = $this->processSingleScheduleCreation($data, $index, $skipConflicts, $results);
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeBatchResults(): array
    {
        return [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function processSingleScheduleCreation(
        array $data,
        int $index,
        bool $skipConflicts,
        array $results,
    ): array {
        try {
            $results = $this->createScheduleFromData($data, $skipConflicts, $results);
            $successCount = $results['success'];
            assert(is_int($successCount));
            $results['success'] = $successCount + 1;
        } catch (\Throwable $e) {
            $results = $this->recordScheduleCreationError($results, $index, $data, $e);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function createScheduleFromData(array $data, bool $skipConflicts, array $results): array
    {
        $classroom = $this->validateAndGetClassroom($data);
        $timeRange = $this->validateAndExtractTimeRange($data);

        $results = $this->handleScheduleConflicts($classroom, $timeRange['start'], $timeRange['end'], $skipConflicts, $results);

        $scheduleParams = $this->extractScheduleParameters($data);

        $this->createSchedule(
            $classroom,
            $scheduleParams['course_id'],
            $scheduleParams['type'],
            $timeRange['start'],
            $timeRange['end'],
            $scheduleParams['options']
        );

        return $results;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAndGetClassroom(array $data): Classroom
    {
        $classroom = $this->classroomRepository->find($data['classroom_id']);

        if (null === $classroom) {
            throw new InvalidArgumentException('教室不存在');
        }

        return $classroom;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{start: \DateTimeImmutable, end: \DateTimeImmutable}
     */
    private function validateAndExtractTimeRange(array $data): array
    {
        if (!isset($data['start_time']) || !is_string($data['start_time'])) {
            throw new InvalidArgumentException('开始时间格式错误');
        }
        if (!isset($data['end_time']) || !is_string($data['end_time'])) {
            throw new InvalidArgumentException('结束时间格式错误');
        }

        return [
            'start' => new \DateTimeImmutable($data['start_time']),
            'end' => new \DateTimeImmutable($data['end_time']),
        ];
    }

    /**
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function handleScheduleConflicts(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        bool $skipConflicts,
        array $results,
    ): array {
        $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);

        if ([] === $conflicts) {
            return $results;
        }

        if ($skipConflicts) {
            $skippedCount = $results['skipped'];
            assert(is_int($skippedCount));
            $results['skipped'] = $skippedCount + 1;
            throw new InvalidArgumentException('跳过冲突排课');
        }

        throw new InvalidArgumentException('排课时间冲突');
    }

    /**
     * @param array<string, mixed> $data
     * @return array{course_id: int, type: ScheduleType, options: array<string, mixed>}
     */
    private function extractScheduleParameters(array $data): array
    {
        if (!isset($data['course_id']) || !is_numeric($data['course_id'])) {
            throw new InvalidArgumentException('课程ID格式错误');
        }
        if (!isset($data['type']) || !is_string($data['type'])) {
            throw new InvalidArgumentException('排课类型格式错误');
        }

        return [
            'course_id' => (int) $data['course_id'],
            'type' => ScheduleType::from($data['type']),
            'options' => $this->extractOptions($data),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function extractOptions(array $data): array
    {
        if (!isset($data['options']) || !is_array($data['options'])) {
            return [];
        }

        /** @var array<string, mixed> $options */
        $options = [];
        foreach ($data['options'] as $key => $value) {
            $options[(string) $key] = $value;
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $results
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function recordScheduleCreationError(array $results, int $index, array $data, \Throwable $e): array
    {
        $failedCount = $results['failed'];
        assert(is_int($failedCount));
        $results['failed'] = $failedCount + 1;

        $errors = $results['errors'];
        assert(is_array($errors));
        $errors[] = [
            'index' => $index,
            'error' => $e->getMessage(),
            'data' => $data,
        ];
        $results['errors'] = $errors;

        return $results;
    }

    public function cancelSchedule(ClassroomSchedule $schedule, string $reason): ClassroomSchedule
    {
        return $this->updateScheduleStatus($schedule, ScheduleStatus::CANCELLED, $reason);
    }

    public function postponeSchedule(
        ClassroomSchedule $schedule,
        \DateTimeInterface $newStartTime,
        \DateTimeInterface $newEndTime,
        string $reason,
    ): ClassroomSchedule {
        // 验证新时间
        if ($newStartTime >= $newEndTime) {
            throw new InvalidArgumentException('新的开始时间必须早于结束时间');
        }

        // 检测新时间是否冲突
        $conflicts = $this->detectScheduleConflicts(
            $schedule->getClassroom(),
            $newStartTime,
            $newEndTime,
            (int) $schedule->getId()
        );

        if ([] !== $conflicts) {
            throw new InvalidArgumentException('新的排课时间冲突');
        }

        // 记录原时间
        $originalStartTime = $schedule->getStartTime()->format('Y-m-d H:i:s');
        $originalEndTime = $schedule->getEndTime()->format('Y-m-d H:i:s');

        // 更新时间
        $schedule->setStartTime(\DateTimeImmutable::createFromInterface($newStartTime));
        $schedule->setEndTime(\DateTimeImmutable::createFromInterface($newEndTime));
        $schedule->setScheduleStatus(ScheduleStatus::POSTPONED);

        // 添加延期备注
        $postponeRemark = "延期：原时间 {$originalStartTime} - {$originalEndTime}，延期原因：{$reason}";
        $currentRemark = $schedule->getRemark();
        $newRemark = (null !== $currentRemark && '' !== $currentRemark) ? $currentRemark . "\n" . $postponeRemark : $postponeRemark;
        $schedule->setRemark($newRemark);

        $this->entityManager->flush();

        $this->logger->info('排课延期', [
            'schedule_id' => $schedule->getId(),
            'original_start' => $originalStartTime,
            'original_end' => $originalEndTime,
            'new_start' => $newStartTime->format('Y-m-d H:i:s'),
            'new_end' => $newEndTime->format('Y-m-d H:i:s'),
            'reason' => $reason,
        ]);

        return $schedule;
    }

    /**
     * @param array<string> $classroomIds
     * @return array<string, mixed>
     */
    public function getScheduleCalendar(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $classroomIds = [],
    ): array {
        $schedules = $this->scheduleRepository->findSchedulesInDateRange(
            $startDate,
            $endDate,
            $classroomIds
        );

        $calendar = [];
        foreach ($schedules as $schedule) {
            $date = $schedule->getStartTime()->format('Y-m-d');
            if (!isset($calendar[$date])) {
                $calendar[$date] = [];
            }

            $calendar[$date][] = [
                'id' => $schedule->getId(),
                'title' => $schedule->getCourseContent() ?? '未设置课程内容',
                'classroom' => $schedule->getClassroom()->getName(),
                'classroom_id' => $schedule->getClassroom()->getId(),
                'teacher_id' => $schedule->getTeacherId(),
                'type' => $schedule->getScheduleType()->value,
                'status' => $schedule->getScheduleStatus()->value,
                'start_time' => $schedule->getStartTime()->format('H:i'),
                'end_time' => $schedule->getEndTime()->format('H:i'),
                'duration' => $schedule->getDurationInMinutes(),
                'expected_students' => $schedule->getExpectedStudents(),
                'actual_students' => $schedule->getActualStudents(),
            ];
        }

        // 按日期排序
        ksort($calendar);

        return $calendar;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getScheduleStatisticsReport(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $filters = [],
    ): array {
        return $this->scheduleRepository->getScheduleStatisticsReport(
            $startDate,
            $endDate,
            $filters
        );
    }
}
