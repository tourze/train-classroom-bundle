<?php

declare(strict_types=1);

namespace Axtk\TrainClassroomBundle\Service;

use Axtk\TrainClassroomBundle\Entity\Classroom;
use Axtk\TrainClassroomBundle\Enum\ClassroomStatus;
use Axtk\TrainClassroomBundle\Enum\ClassroomType;

/**
 * 教室管理服务接口
 * 
 * 提供教室的创建、更新、查询、状态管理等核心业务功能
 */
interface ClassroomServiceInterface
{
    /**
     * 创建新教室
     * 
     * @param array $data 教室数据
     * @return Classroom
     */
    public function createClassroom(array $data): Classroom;

    /**
     * 更新教室信息
     * 
     * @param Classroom $classroom 教室实体
     * @param array $data 更新数据
     * @return Classroom
     */
    public function updateClassroom(Classroom $classroom, array $data): Classroom;

    /**
     * 删除教室
     * 
     * @param Classroom $classroom 教室实体
     * @return bool
     */
    public function deleteClassroom(Classroom $classroom): bool;

    /**
     * 根据ID获取教室
     * 
     * @param int $id 教室ID
     * @return Classroom|null
     */
    public function getClassroomById(int $id): ?Classroom;

    /**
     * 获取可用教室列表
     * 
     * @param ClassroomType|null $type 教室类型
     * @param int|null $minCapacity 最小容量
     * @param array $filters 其他过滤条件
     * @return array
     */
    public function getAvailableClassrooms(?ClassroomType $type = null, ?int $minCapacity = null, array $filters = []): array;

    /**
     * 更新教室状态
     * 
     * @param Classroom $classroom 教室实体
     * @param ClassroomStatus $status 新状态
     * @param string|null $reason 状态变更原因
     * @return Classroom
     */
    public function updateClassroomStatus(Classroom $classroom, ClassroomStatus $status, ?string $reason = null): Classroom;

    /**
     * 检查教室是否可用
     * 
     * @param Classroom $classroom 教室实体
     * @param \DateTimeInterface $startTime 开始时间
     * @param \DateTimeInterface $endTime 结束时间
     * @return bool
     */
    public function isClassroomAvailable(Classroom $classroom, \DateTimeInterface $startTime, \DateTimeInterface $endTime): bool;

    /**
     * 获取教室使用统计
     * 
     * @param Classroom $classroom 教室实体
     * @param \DateTimeInterface $startDate 开始日期
     * @param \DateTimeInterface $endDate 结束日期
     * @return array
     */
    public function getClassroomUsageStats(Classroom $classroom, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;

    /**
     * 获取教室设备列表
     * 
     * @param Classroom $classroom 教室实体
     * @return array
     */
    public function getClassroomDevices(Classroom $classroom): array;

    /**
     * 更新教室设备配置
     * 
     * @param Classroom $classroom 教室实体
     * @param array $devices 设备配置
     * @return Classroom
     */
    public function updateClassroomDevices(Classroom $classroom, array $devices): Classroom;

    /**
     * 获取教室环境监控数据
     * 
     * @param Classroom $classroom 教室实体
     * @param \DateTimeInterface|null $startTime 开始时间
     * @param \DateTimeInterface|null $endTime 结束时间
     * @return array
     */
    public function getEnvironmentData(Classroom $classroom, ?\DateTimeInterface $startTime = null, ?\DateTimeInterface $endTime = null): array;

    /**
     * 批量导入教室数据
     * 
     * @param array $classroomsData 教室数据数组
     * @param bool $dryRun 是否试运行
     * @return array 导入结果
     */
    public function batchImportClassrooms(array $classroomsData, bool $dryRun = false): array;
} 