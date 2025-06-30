<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

/**
 * 排课服务实现
 *
 * 提供教室排课管理的核心业务功能实现
 */
class ScheduleService implements ScheduleServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly ClassroomRepository $classroomRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createSchedule(
        Classroom $classroom,
        int $courseId,
        ScheduleType $type,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        array $options = []
    ): ClassroomSchedule {
        // 验证时间参数
        if ($startTime >= $endTime) {
            throw new InvalidArgumentException('开始时间必须早于结束时间');
        }

        // 检测时间冲突
        $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);
        if (!empty($conflicts)) {
            throw new InvalidArgumentException('排课时间冲突，已存在' . count($conflicts) . '个冲突的排课');
        }

        // 创建排课记录
        $schedule = new ClassroomSchedule();
        $schedule->setClassroom($classroom);
        $schedule->setTeacherId((string)$courseId);
        $schedule->setScheduleType($type);
        $schedule->setStartTime(\DateTimeImmutable::createFromInterface($startTime));
        $schedule->setEndTime(\DateTimeImmutable::createFromInterface($endTime));
        $schedule->setScheduleStatus(ScheduleStatus::SCHEDULED);
        
        // 设置可选参数
        if (isset($options['course_content'])) {
            $schedule->setCourseContent($options['course_content']);
        }
        if (isset($options['expected_students'])) {
            $schedule->setExpectedStudents($options['expected_students']);
        }
        if (isset($options['remark'])) {
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

    public function detectScheduleConflicts(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeScheduleId = null
    ): array {
        return $this->scheduleRepository->findConflictingSchedules(
            $classroom,
            \DateTimeImmutable::createFromInterface($startTime),
            \DateTimeImmutable::createFromInterface($endTime),
            $excludeScheduleId !== null ? (string)$excludeScheduleId : null
        );
    }

    public function updateScheduleStatus(
        ClassroomSchedule $schedule,
        ScheduleStatus $status,
        ?string $reason = null
    ): ClassroomSchedule {
        $oldStatus = $schedule->getScheduleStatus();
        $schedule->setScheduleStatus($status);
        
        if ($reason !== null) {
            $currentRemark = $schedule->getRemark();
            $newRemark = ($currentRemark !== null && $currentRemark !== '')
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

    public function getClassroomUtilizationRate(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        return $this->scheduleRepository->getClassroomUtilizationRate(
            $classroom,
            $startDate,
            $endDate
        );
    }

    public function findAvailableClassrooms(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $minCapacity = null,
        array $requiredFeatures = []
    ): array {
        // 获取所有教室
        $classrooms = $this->classroomRepository->findAll();
        $availableClassrooms = [];

        foreach ($classrooms as $classroom) {
            // 检查容量要求
            if ($minCapacity !== null && $classroom->getCapacity() < $minCapacity) {
                continue;
            }

            // 检查设施要求
            if (!empty($requiredFeatures)) {
                $classroomFeatures = $classroom->getDevices() ?? [];
                $hasAllFeatures = true;
                foreach ($requiredFeatures as $feature) {
                    if (!in_array($feature, $classroomFeatures)) {
                        $hasAllFeatures = false;
                        break;
                    }
                }
                if (!$hasAllFeatures) {
                    continue;
                }
            }

            // 检查时间冲突
            $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);
            if ((bool) empty($conflicts)) {
                $availableClassrooms[] = [
                    'classroom' => $classroom,
                    'capacity' => $classroom->getCapacity(),
                    'devices' => $classroom->getDevices(),
                    'location' => $classroom->getLocation(),
                ];
            }
        }

        // 按容量排序
        usort($availableClassrooms, fn($a, $b) => $b['capacity'] <=> $a['capacity']);

        return $availableClassrooms;
    }

    public function batchCreateSchedules(array $scheduleData, bool $skipConflicts = false): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($scheduleData as $index => $data) {
            try {
                $classroom = $this->classroomRepository->find($data['classroom_id']);

                if (($classroom === null)) {
                    throw new InvalidArgumentException('教室不存在');
                }

                $startTime = new \DateTimeImmutable($data['start_time']);
                $endTime = new \DateTimeImmutable($data['end_time']);

                // 检查冲突
                $conflicts = $this->detectScheduleConflicts($classroom, $startTime, $endTime);
                if (!empty($conflicts)) {
                    if ($skipConflicts) {
                        $results['skipped']++;
                        continue;
                    } else {
                        throw new InvalidArgumentException('排课时间冲突');
                    }
                }

                $this->createSchedule(
                    $classroom,
                    $data['course_id'],
                    ScheduleType::from($data['type']),
                    $startTime,
                    $endTime,
                    $data['options'] ?? []
                );

                $results['success']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'error' => $e->getMessage(),
                    'data' => $data,
                ];
            }
        }

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
        string $reason
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
            (int)$schedule->getId()
        );

        if (!empty($conflicts)) {
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
        $newRemark = ($currentRemark !== null && $currentRemark !== '') ? $currentRemark . "\n" . $postponeRemark : $postponeRemark;
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

    public function getScheduleCalendar(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $classroomIds = []
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

    public function getScheduleStatisticsReport(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $filters = []
    ): array {
        return $this->scheduleRepository->getScheduleStatisticsReport(
            $startDate,
            $endDate,
            $filters
        );
    }
} 