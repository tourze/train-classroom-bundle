<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Repository\AttendanceRecordRepository;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

/**
 * 考勤服务实现
 *
 * 提供培训考勤管理的核心业务功能实现
 */
#[WithMonologChannel(channel: 'train_classroom')]
class AttendanceService implements AttendanceServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttendanceRecordRepository $attendanceRepository,
        private readonly RegistrationRepository $registrationRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $deviceData
     */
    public function recordAttendance(
        Registration $registration,
        AttendanceType $type,
        AttendanceMethod $method,
        array $deviceData = [],
        ?string $remark = null,
    ): AttendanceRecord {
        // 验证考勤有效性
        if (!$this->validateAttendance($registration, $type)) {
            throw new InvalidArgumentException('考勤记录无效：时间或状态不符合要求');
        }

        // 创建考勤记录
        $attendance = new AttendanceRecord();
        $attendance->setRegistration($registration);
        $attendance->setType($type);
        $attendance->setMethod($method);
        $attendance->setRecordTime(new \DateTimeImmutable());
        $attendance->setDeviceData($deviceData);
        $attendance->setRemark($remark);
        $attendance->setVerificationResult(VerificationResult::SUCCESS);

        $this->entityManager->persist($attendance);
        $this->entityManager->flush();

        $this->logger->info('考勤记录创建成功', [
            'attendance_id' => $attendance->getId(),
            'registration_id' => $registration->getId(),
            'type' => $type->value,
            'method' => $method->value,
        ]);

        return $attendance;
    }

    /**
     * @param array<int, array<string, mixed>> $attendanceData
     * @return array<string, mixed>
     */
    public function batchImportAttendance(array $attendanceData): array
    {
        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        foreach ($attendanceData as $index => $data) {
            $results = $this->processSingleAttendanceImport($data, $index, $results);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function processSingleAttendanceImport(array $data, int $index, array $results): array
    {
        try {
            $registration = $this->validateAndGetRegistration($data);
            $attendanceParams = $this->extractAttendanceParameters($data);

            $this->recordAttendance(
                $registration,
                $attendanceParams['type'],
                $attendanceParams['method'],
                $attendanceParams['deviceData'],
                $attendanceParams['remark']
            );

            $successCount = is_int($results['success']) ? $results['success'] : 0;
            $results['success'] = $successCount + 1;
        } catch (\Throwable $e) {
            $results = $this->recordAttendanceImportError($results, $index, $data, $e);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAndGetRegistration(array $data): Registration
    {
        $registrationId = $data['registration_id'] ?? null;
        if (null === $registrationId) {
            throw new InvalidArgumentException('报名记录ID不能为空');
        }

        $registration = $this->registrationRepository->find($registrationId);
        if (null === $registration) {
            throw new InvalidArgumentException('报名记录不存在');
        }

        return $registration;
    }

    /**
     * @param array<string, mixed> $data
     * @return array{type: AttendanceType, method: AttendanceMethod, deviceData: array<string, mixed>, remark: string|null}
     */
    private function extractAttendanceParameters(array $data): array
    {
        $type = $this->validateAndExtractType($data);
        $method = $this->validateAndExtractMethod($data);
        $deviceData = $this->normalizeDeviceData($data);
        $remark = $this->validateAndExtractRemark($data);

        return [
            'type' => $type,
            'method' => $method,
            'deviceData' => $deviceData,
            'remark' => $remark,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAndExtractType(array $data): AttendanceType
    {
        $type = $data['type'] ?? null;
        if (null === $type) {
            throw new InvalidArgumentException('考勤类型不能为空');
        }

        if (!is_int($type) && !is_string($type)) {
            throw new InvalidArgumentException('考勤类型必须是整数或字符串');
        }

        return AttendanceType::from($type);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAndExtractMethod(array $data): AttendanceMethod
    {
        $method = $data['method'] ?? null;
        if (null === $method) {
            throw new InvalidArgumentException('考勤方式不能为空');
        }

        if (!is_int($method) && !is_string($method)) {
            throw new InvalidArgumentException('考勤方式必须是整数或字符串');
        }

        return AttendanceMethod::from($method);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDeviceData(array $data): array
    {
        $deviceData = $data['device_data'] ?? [];
        if (!is_array($deviceData)) {
            throw new InvalidArgumentException('设备数据必须是数组类型');
        }

        /** @var array<string, mixed> $normalizedDeviceData */
        $normalizedDeviceData = [];
        foreach ($deviceData as $key => $value) {
            $normalizedDeviceData[(string) $key] = $value;
        }

        return $normalizedDeviceData;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateAndExtractRemark(array $data): ?string
    {
        $remark = $data['remark'] ?? null;
        if (null !== $remark && !is_string($remark)) {
            throw new InvalidArgumentException('备注必须是字符串类型');
        }

        return $remark;
    }

    /**
     * @param array<string, mixed> $results
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function recordAttendanceImportError(array $results, int $index, array $data, \Throwable $e): array
    {
        $failedCount = is_int($results['failed']) ? $results['failed'] : 0;
        $results['failed'] = $failedCount + 1;

        if (!is_array($results['errors'])) {
            $results['errors'] = [];
        }

        $results['errors'][] = [
            'index' => $index,
            'error' => $e->getMessage(),
            'data' => $data,
        ];

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAttendanceStatistics(Registration $registration): array
    {
        $records = $this->attendanceRepository->findByRegistration($registration);

        $statistics = [
            'total_records' => count($records),
            'sign_in_count' => 0,
            'sign_out_count' => 0,
            'break_out_count' => 0,
            'break_return_count' => 0,
            'attendance_days' => [],
            'attendance_rate' => 0,
        ];

        $attendanceDays = [];

        foreach ($records as $record) {
            $day = $record->getRecordTime()->format('Y-m-d');
            $attendanceDays[$day] = true;

            switch ($record->getType()) {
                case AttendanceType::SIGN_IN:
                    $statistics['sign_in_count']++;
                    break;
                case AttendanceType::SIGN_OUT:
                    $statistics['sign_out_count']++;
                    break;
                case AttendanceType::BREAK_OUT:
                    $statistics['break_out_count']++;
                    break;
                case AttendanceType::BREAK_IN:
                    $statistics['break_return_count']++;
                    break;
            }
        }

        $statistics['attendance_days'] = array_keys($attendanceDays);
        $statistics['unique_days'] = count($attendanceDays);

        // 计算考勤率（基于课程总天数）
        $totalCourseDays = $this->calculateTotalCourseDays($registration);
        if ($totalCourseDays > 0) {
            $statistics['attendance_rate'] = round(
                ($statistics['unique_days'] / $totalCourseDays) * 100,
                2
            );
        }

        return $statistics;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCourseAttendanceSummary(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        // 检查课程是否存在
        $courseExists = $this->registrationRepository->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.course', 'c')
            ->where('c.id = :courseId')
            ->setParameter('courseId', $courseId)
            ->getQuery()
            ->getSingleScalarResult() > 0
        ;

        if (!$courseExists) {
            throw new InvalidArgumentException('课程不存在');
        }

        $summary = $this->attendanceRepository->getCourseAttendanceSummary(
            $courseId,
            $startDate,
            $endDate
        );

        // 计算汇总统计
        $totalStudents = count($summary);
        $totalAttendanceRecords = array_sum(array_column($summary, 'total_records'));
        $averageAttendanceRate = $totalStudents > 0
            ? round(array_sum(array_column($summary, 'attendance_rate')) / $totalStudents, 2)
            : 0;

        return [
            'course_id' => $courseId,
            'period' => [
                'start_date' => $startDate?->format('Y-m-d'),
                'end_date' => $endDate?->format('Y-m-d'),
            ],
            'summary' => [
                'total_students' => $totalStudents,
                'total_attendance_records' => $totalAttendanceRecords,
                'average_attendance_rate' => $averageAttendanceRate,
            ],
            'student_details' => $summary,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function detectAttendanceAnomalies(
        Registration $registration,
        ?\DateTimeInterface $date = null,
    ): array {
        $date ??= new \DateTimeImmutable();
        $startOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59, 999999);

        $records = $this->attendanceRepository->findByRegistrationAndDateRange(
            $registration,
            $startOfDay,
            $endOfDay
        );

        $anomalies = [];

        // 检测异常情况
        $signInRecords = array_filter($records, fn ($r) => AttendanceType::SIGN_IN === $r->getType());
        $signOutRecords = array_filter($records, fn ($r) => AttendanceType::SIGN_OUT === $r->getType());

        // 异常1：多次签到
        if (count($signInRecords) > 1) {
            $anomalies[] = [
                'type' => 'multiple_sign_in',
                'message' => '当日存在多次签到记录',
                'records' => array_values($signInRecords),
            ];
        }

        // 异常2：多次签退
        if (count($signOutRecords) > 1) {
            $anomalies[] = [
                'type' => 'multiple_sign_out',
                'message' => '当日存在多次签退记录',
                'records' => array_values($signOutRecords),
            ];
        }

        // 异常3：只有签退没有签到
        if (count($signOutRecords) > 0 && 0 === count($signInRecords)) {
            $anomalies[] = [
                'type' => 'sign_out_without_sign_in',
                'message' => '存在签退记录但无签到记录',
                'records' => array_values($signOutRecords),
            ];
        }

        // 异常4：签退时间早于签到时间
        if (count($signInRecords) > 0 && count($signOutRecords) > 0) {
            $latestSignIn = max(array_map(fn ($r) => $r->getRecordTime(), $signInRecords));
            $earliestSignOut = min(array_map(fn ($r) => $r->getRecordTime(), $signOutRecords));

            if ($earliestSignOut < $latestSignIn) {
                $anomalies[] = [
                    'type' => 'sign_out_before_sign_in',
                    'message' => '签退时间早于签到时间',
                    'sign_in_time' => $latestSignIn->format('H:i:s'),
                    'sign_out_time' => $earliestSignOut->format('H:i:s'),
                ];
            }
        }

        return $anomalies;
    }

    public function makeUpAttendance(
        Registration $registration,
        AttendanceType $type,
        \DateTimeInterface $recordTime,
        string $reason,
    ): AttendanceRecord {
        $attendance = new AttendanceRecord();
        $attendance->setRegistration($registration);
        $attendance->setType($type);
        $attendance->setMethod(AttendanceMethod::MANUAL);
        $attendance->setRecordTime($recordTime);
        $attendance->setRemark("补录考勤：{$reason}");
        $attendance->setVerificationResult(VerificationResult::SUCCESS);

        $this->entityManager->persist($attendance);
        $this->entityManager->flush();

        $this->logger->info('补录考勤记录', [
            'attendance_id' => $attendance->getId(),
            'registration_id' => $registration->getId(),
            'type' => $type->value,
            'record_time' => $recordTime->format('Y-m-d H:i:s'),
            'reason' => $reason,
        ]);

        return $attendance;
    }

    public function validateAttendance(
        Registration $registration,
        AttendanceType $type,
        ?\DateTimeInterface $recordTime = null,
    ): bool {
        $recordTime ??= new \DateTimeImmutable();

        // 检查报名状态是否有效
        if (!$registration->isActive()) {
            return false;
        }

        // 检查是否在课程时间范围内
        // TODO: Course 实体需要添加时间范围属性

        // 检查当日是否已有相同类型的考勤记录
        $startOfDay = \DateTimeImmutable::createFromInterface($recordTime)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($recordTime)->setTime(23, 59, 59);

        $existingRecords = $this->attendanceRepository->findByRegistrationTypeAndDateRange(
            $registration,
            $type,
            $startOfDay,
            $endOfDay
        );

        // 签到和签退每天只能有一次
        if (in_array($type, [AttendanceType::SIGN_IN, AttendanceType::SIGN_OUT], true) && count($existingRecords) > 0) {
            return false;
        }

        return true;
    }

    /**
     * @return array<array<string, float|int>>
     */
    public function getAttendanceRateStatistics(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array {
        return $this->attendanceRepository->getAttendanceRateStatistics(
            $courseId,
            $startDate,
            $endDate
        );
    }

    /**
     * 计算课程总天数
     */
    private function calculateTotalCourseDays(Registration $registration): int
    {
        // TODO: Course 实体需要添加 getStartTime() 和 getEndTime() 方法
        // $course = $registration->getCourse();
        // if (!$course || !$course->getStartTime() || ($course->getEndTime() === null)) {
        //     return 0;
        // }

        // 临时使用报名时间作为课程时间
        $startDate = $registration->getBeginTime();
        $endDate = $registration->getEndTime();
        if (null === $endDate) {
            return 0;
        }
        $interval = $startDate->diff($endDate);

        return (false !== $interval->days ? $interval->days : 0) + 1;
    }
}
