<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * 排课服务接口
 * 
 * 提供教室排课管理的核心业务功能，包括排课冲突检测、资源调度、使用率统计等
 */
interface ScheduleServiceInterface
{
    /**
     * 创建排课
     *
     * @param Classroom $classroom 教室
     * @param int $courseId 课程ID
     * @param ScheduleType $type 排课类型
     * @param \DateTimeInterface $startTime 开始时间
     * @param \DateTimeInterface $endTime 结束时间
     * @param array $options 额外选项
     * @return ClassroomSchedule
     * @throws \InvalidArgumentException 当时间冲突或参数无效时
     */
    public function createSchedule(
        Classroom $classroom,
        int $courseId,
        ScheduleType $type,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        array $options = []
    ): ClassroomSchedule;

    /**
     * 检测排课冲突
     *
     * @param Classroom $classroom 教室
     * @param \DateTimeInterface $startTime 开始时间
     * @param \DateTimeInterface $endTime 结束时间
     * @param int|null $excludeScheduleId 排除的排课ID（用于更新时）
     * @return array 冲突的排课列表
     */
    public function detectScheduleConflicts(
        Classroom $classroom,
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $excludeScheduleId = null
    ): array;

    /**
     * 更新排课状态
     *
     * @param ClassroomSchedule $schedule 排课记录
     * @param ScheduleStatus $status 新状态
     * @param string|null $reason 状态变更原因
     * @return ClassroomSchedule
     */
    public function updateScheduleStatus(
        ClassroomSchedule $schedule,
        ScheduleStatus $status,
        ?string $reason = null
    ): ClassroomSchedule;

    /**
     * 获取教室使用率统计
     *
     * @param Classroom $classroom 教室
     * @param \DateTimeInterface $startDate 开始日期
     * @param \DateTimeInterface $endDate 结束日期
     * @return array 使用率统计数据
     */
    public function getClassroomUtilizationRate(
        Classroom $classroom,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array;

    /**
     * 查找可用教室
     *
     * @param \DateTimeInterface $startTime 开始时间
     * @param \DateTimeInterface $endTime 结束时间
     * @param int|null $minCapacity 最小容量要求
     * @param array $requiredFeatures 必需设施特性
     * @return array 可用教室列表
     */
    public function findAvailableClassrooms(
        \DateTimeInterface $startTime,
        \DateTimeInterface $endTime,
        ?int $minCapacity = null,
        array $requiredFeatures = []
    ): array;

    /**
     * 批量排课
     *
     * @param array $scheduleData 排课数据数组
     * @param bool $skipConflicts 是否跳过冲突的排课
     * @return array 批量排课结果
     */
    public function batchCreateSchedules(array $scheduleData, bool $skipConflicts = false): array;

    /**
     * 取消排课
     *
     * @param ClassroomSchedule $schedule 排课记录
     * @param string $reason 取消原因
     * @return ClassroomSchedule
     */
    public function cancelSchedule(ClassroomSchedule $schedule, string $reason): ClassroomSchedule;

    /**
     * 延期排课
     *
     * @param ClassroomSchedule $schedule 排课记录
     * @param \DateTimeInterface $newStartTime 新开始时间
     * @param \DateTimeInterface $newEndTime 新结束时间
     * @param string $reason 延期原因
     * @return ClassroomSchedule
     */
    public function postponeSchedule(
        ClassroomSchedule $schedule,
        \DateTimeInterface $newStartTime,
        \DateTimeInterface $newEndTime,
        string $reason
    ): ClassroomSchedule;

    /**
     * 获取排课日历数据
     *
     * @param \DateTimeInterface $startDate 开始日期
     * @param \DateTimeInterface $endDate 结束日期
     * @param array $classroomIds 教室ID列表，为空则查询所有
     * @return array 日历数据
     */
    public function getScheduleCalendar(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $classroomIds = []
    ): array;

    /**
     * 获取排课统计报表
     *
     * @param \DateTimeInterface $startDate 开始日期
     * @param \DateTimeInterface $endDate 结束日期
     * @param array $filters 过滤条件
     * @return array 统计报表数据
     */
    public function getScheduleStatisticsReport(
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        array $filters = []
    ): array;
} 