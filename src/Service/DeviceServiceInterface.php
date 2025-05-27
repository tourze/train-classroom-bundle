<?php

declare(strict_types=1);

namespace Axtk\TrainClassroomBundle\Service;

use Axtk\TrainClassroomBundle\Entity\Classroom;
use Axtk\TrainClassroomBundle\Enum\AttendanceMethod;

/**
 * 设备集成服务接口
 * 
 * 提供考勤设备、监控设备等的集成和管理功能
 */
interface DeviceServiceInterface
{
    /**
     * 获取教室设备列表
     * 
     * @param Classroom $classroom 教室实体
     * @return array
     */
    public function getClassroomDevices(Classroom $classroom): array;

    /**
     * 添加设备到教室
     * 
     * @param Classroom $classroom 教室实体
     * @param array $deviceConfig 设备配置
     * @return array
     */
    public function addDevice(Classroom $classroom, array $deviceConfig): array;

    /**
     * 移除教室设备
     * 
     * @param Classroom $classroom 教室实体
     * @param string $deviceId 设备ID
     * @return bool
     */
    public function removeDevice(Classroom $classroom, string $deviceId): bool;

    /**
     * 更新设备配置
     * 
     * @param Classroom $classroom 教室实体
     * @param string $deviceId 设备ID
     * @param array $config 新配置
     * @return array
     */
    public function updateDeviceConfig(Classroom $classroom, string $deviceId, array $config): array;

    /**
     * 检查设备状态
     * 
     * @param Classroom $classroom 教室实体
     * @param string|null $deviceId 设备ID，为空时检查所有设备
     * @return array
     */
    public function checkDeviceStatus(Classroom $classroom, ?string $deviceId = null): array;

    /**
     * 测试设备连接
     * 
     * @param array $deviceConfig 设备配置
     * @return array
     */
    public function testDeviceConnection(array $deviceConfig): array;

    /**
     * 获取支持的考勤方式
     * 
     * @param Classroom $classroom 教室实体
     * @return array
     */
    public function getSupportedAttendanceMethods(Classroom $classroom): array;

    /**
     * 执行考勤验证
     * 
     * @param Classroom $classroom 教室实体
     * @param AttendanceMethod $method 考勤方式
     * @param array $data 验证数据
     * @return array
     */
    public function performAttendanceVerification(Classroom $classroom, AttendanceMethod $method, array $data): array;

    /**
     * 获取监控设备录像
     * 
     * @param Classroom $classroom 教室实体
     * @param \DateTimeInterface $startTime 开始时间
     * @param \DateTimeInterface $endTime 结束时间
     * @return array
     */
    public function getRecordings(Classroom $classroom, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array;

    /**
     * 开始录像
     * 
     * @param Classroom $classroom 教室实体
     * @param array $options 录像选项
     * @return array
     */
    public function startRecording(Classroom $classroom, array $options = []): array;

    /**
     * 停止录像
     * 
     * @param Classroom $classroom 教室实体
     * @return array
     */
    public function stopRecording(Classroom $classroom): array;

    /**
     * 获取环境监控数据
     * 
     * @param Classroom $classroom 教室实体
     * @param array $sensors 传感器类型
     * @return array
     */
    public function getEnvironmentData(Classroom $classroom, array $sensors = []): array;

    /**
     * 设置环境监控阈值
     * 
     * @param Classroom $classroom 教室实体
     * @param array $thresholds 阈值配置
     * @return bool
     */
    public function setEnvironmentThresholds(Classroom $classroom, array $thresholds): bool;

    /**
     * 获取设备日志
     * 
     * @param Classroom $classroom 教室实体
     * @param string|null $deviceId 设备ID
     * @param \DateTimeInterface|null $startTime 开始时间
     * @param \DateTimeInterface|null $endTime 结束时间
     * @return array
     */
    public function getDeviceLogs(Classroom $classroom, ?string $deviceId = null, ?\DateTimeInterface $startTime = null, ?\DateTimeInterface $endTime = null): array;

    /**
     * 同步设备数据
     * 
     * @param Classroom $classroom 教室实体
     * @param array $options 同步选项
     * @return array
     */
    public function syncDeviceData(Classroom $classroom, array $options = []): array;
} 