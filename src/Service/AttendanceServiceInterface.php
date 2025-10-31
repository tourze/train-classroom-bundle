<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;

/**
 * 考勤服务接口
 *
 * 提供培训考勤管理的核心业务功能，包括签到签退、考勤统计、异常处理等
 */
interface AttendanceServiceInterface
{
    /**
     * 记录考勤
     *
     * @param Registration     $registration 报名记录
     * @param AttendanceType   $type         考勤类型
     * @param AttendanceMethod $method       考勤方式
     * @param array<string, mixed> $deviceData   设备数据（如人脸特征、指纹数据等）
     * @param string|null      $remark       备注
     */
    public function recordAttendance(
        Registration $registration,
        AttendanceType $type,
        AttendanceMethod $method,
        array $deviceData = [],
        ?string $remark = null,
    ): AttendanceRecord;

    /**
     * 批量导入考勤记录
     *
     * @param array<int, array<string, mixed>> $attendanceData 考勤数据数组
     *
     * @return array<string, mixed> 导入结果统计
     */
    public function batchImportAttendance(array $attendanceData): array;

    /**
     * 获取学员考勤统计
     *
     * @param Registration $registration 报名记录
     *
     * @return array<string, mixed> 考勤统计数据
     */
    public function getAttendanceStatistics(Registration $registration): array;

    /**
     * 获取课程考勤汇总
     *
     * @param int                     $courseId  课程ID
     * @param \DateTimeInterface|null $startDate 开始日期
     * @param \DateTimeInterface|null $endDate   结束日期
     *
     * @return array<string, mixed> 考勤汇总数据
     *
     * @throws InvalidArgumentException 当课程不存在时
     */
    public function getCourseAttendanceSummary(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array;

    /**
     * 检测考勤异常
     *
     * @param Registration            $registration 报名记录
     * @param \DateTimeInterface|null $date         检测日期，默认今天
     *
     * @return array<int, array<string, mixed>> 异常记录列表
     */
    public function detectAttendanceAnomalies(
        Registration $registration,
        ?\DateTimeInterface $date = null,
    ): array;

    /**
     * 补录考勤记录
     *
     * @param Registration       $registration 报名记录
     * @param AttendanceType     $type         考勤类型
     * @param \DateTimeInterface $recordTime   补录时间
     * @param string             $reason       补录原因
     */
    public function makeUpAttendance(
        Registration $registration,
        AttendanceType $type,
        \DateTimeInterface $recordTime,
        string $reason,
    ): AttendanceRecord;

    /**
     * 验证考勤有效性
     *
     * @param Registration            $registration 报名记录
     * @param AttendanceType          $type         考勤类型
     * @param \DateTimeInterface|null $recordTime   记录时间
     *
     * @return bool 是否有效
     */
    public function validateAttendance(
        Registration $registration,
        AttendanceType $type,
        ?\DateTimeInterface $recordTime = null,
    ): bool;

    /**
     * 获取考勤率统计
     *
     * @param int                     $courseId  课程ID
     * @param \DateTimeInterface|null $startDate 开始日期
     * @param \DateTimeInterface|null $endDate   结束日期
     *
     * @return array<array<string, float|int>> 考勤率统计
     */
    public function getAttendanceRateStatistics(
        int $courseId,
        ?\DateTimeInterface $startDate = null,
        ?\DateTimeInterface $endDate = null,
    ): array;
}
