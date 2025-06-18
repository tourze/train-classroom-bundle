<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Repository\AttendanceRecordRepository;

/**
 * 考勤服务实现
 * 
 * 提供培训考勤管理的核心业务功能实现
 */
class AttendanceService implements AttendanceServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AttendanceRecordRepository $attendanceRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function recordAttendance(
        Registration $registration,
        AttendanceType $type,
        AttendanceMethod $method,
        array $deviceData = [],
        ?string $remark = null
    ): AttendanceRecord {
        // 验证考勤有效性
        if (!$this->validateAttendance($registration, $type)) {
            throw new \InvalidArgumentException('考勤记录无效：时间或状态不符合要求');
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

    public function batchImportAttendance(array $attendanceData): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($attendanceData as $index => $data) {
            try {
                $registration = $this->entityManager->getRepository(Registration::class)
                    ->find($data['registration_id']);

                if (($registration === null)) {
                    throw new \InvalidArgumentException('报名记录不存在');
                }

                $this->recordAttendance(
                    $registration,
                    AttendanceType::from($data['type']),
                    AttendanceMethod::from($data['method']),
                    $data['device_data'] ?? [],
                    $data['remark'] ?? null
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

    public function getCourseAttendanceSummary(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
    ): array {
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

    public function detectAttendanceAnomalies(
        Registration $registration,
        ?\DateTimeInterface $date = null
    ): array {
        $date = $date ?? new \DateTimeImmutable();
        $startOfDay = $date->setTime(0, 0, 0, 0);
        $endOfDay = $date->setTime(23, 59, 59, 999999);

        $records = $this->attendanceRepository->findByRegistrationAndDateRange(
            $registration,
            $startOfDay,
            $endOfDay
        );

        $anomalies = [];

        // 检测异常情况
        $signInRecords = array_filter($records, fn($r) => $r->getType() === AttendanceType::SIGN_IN);
        $signOutRecords = array_filter($records, fn($r) => $r->getType() === AttendanceType::SIGN_OUT);

        // 异常1：多次签到
        if ((bool) count($signInRecords) > 1) {
            $anomalies[] = [
                'type' => 'multiple_sign_in',
                'message' => '当日存在多次签到记录',
                'records' => array_values($signInRecords),
            ];
        }

        // 异常2：多次签退
        if ((bool) count($signOutRecords) > 1) {
            $anomalies[] = [
                'type' => 'multiple_sign_out',
                'message' => '当日存在多次签退记录',
                'records' => array_values($signOutRecords),
            ];
        }

        // 异常3：只有签退没有签到
        if ((bool) count($signOutRecords) > 0 && count($signInRecords) === 0) {
            $anomalies[] = [
                'type' => 'sign_out_without_sign_in',
                'message' => '存在签退记录但无签到记录',
                'records' => array_values($signOutRecords),
            ];
        }

        // 异常4：签退时间早于签到时间
        if ((bool) count($signInRecords) > 0 && count($signOutRecords) > 0) {
            $latestSignIn = max(array_map(fn($r) => $r->getRecordTime(), $signInRecords));
            $earliestSignOut = min(array_map(fn($r) => $r->getRecordTime(), $signOutRecords));

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
        string $reason
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
        ?\DateTimeInterface $recordTime = null
    ): bool {
        $recordTime = $recordTime ?? new \DateTimeImmutable();

        // 检查报名状态是否有效
        if (!$registration->isActive()) {
            return false;
        }

        // 检查是否在课程时间范围内
        $course = $registration->getCourse();
        if ($course && $course->getEndTime() && $recordTime > $course->getEndTime()) {
            return false;
        }

        // 检查当日是否已有相同类型的考勤记录
        $startOfDay = $recordTime->setTime(0, 0, 0);
        $endOfDay = $recordTime->setTime(23, 59, 59);

        $existingRecords = $this->attendanceRepository->findByRegistrationTypeAndDateRange(
            $registration,
            $type,
            $startOfDay,
            $endOfDay
        );

        // 签到和签退每天只能有一次
        if ((bool) in_array($type, [AttendanceType::SIGN_IN, AttendanceType::SIGN_OUT]) && count($existingRecords) > 0) {
            return false;
        }

        return true;
    }

    public function getAttendanceRateStatistics(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null
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
        $course = $registration->getCourse();
        if (!$course || !$course->getStartTime() || ($course->getEndTime() === null)) {
            return 0;
        }

        $startDate = $course->getStartTime();
        $endDate = $course->getEndTime();
        $interval = $startDate->diff($endDate);

        return $interval->days + 1;
    }
} 