<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * 设备集成服务实现
 * 
 * 提供考勤设备、监控设备等的集成和管理功能
 */
class DeviceService implements DeviceServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getClassroomDevices(Classroom $classroom): array
    {
        $devices = $classroom->getDevices() ?? [];
        
        // 获取设备状态
        foreach ($devices as &$device) {
            $device['status'] = $this->getDeviceStatus($device);
        }
        
        return $devices;
    }

    public function addDevice(Classroom $classroom, array $deviceConfig): array
    {
        $devices = $this->getClassroomDevices($classroom);
        
        // 验证设备配置
        $this->validateDeviceConfig($deviceConfig);
        
        // 生成设备ID
        $deviceId = $deviceConfig['id'] ?? uniqid('device_');
        $deviceConfig['id'] = $deviceId;
        $deviceConfig['added_at'] = new \DateTime();
        
        // 测试设备连接
        $connectionTest = $this->testDeviceConnection($deviceConfig);
        if (!$connectionTest['success']) {
            throw new \RuntimeException('设备连接测试失败: ' . $connectionTest['error']);
        }
        
        $devices[$deviceId] = $deviceConfig;
        $classroom->setDevices($devices);
        
        $this->entityManager->flush();
        
        $this->logger->info('设备添加成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceId,
            'device_type' => $deviceConfig['type'] ?? 'unknown',
        ]);
        
        return $deviceConfig;
    }

    public function removeDevice(Classroom $classroom, string $deviceId): bool
    {
        $devices = $this->getClassroomDevices($classroom);
        
        if (!isset($devices[$deviceId])) {
            return false;
        }
        
        unset($devices[$deviceId]);
        $classroom->setDevices($devices);
        
        $this->entityManager->flush();
        
        $this->logger->info('设备移除成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceId,
        ]);
        
        return true;
    }

    public function updateDeviceConfig(Classroom $classroom, string $deviceId, array $config): array
    {
        $devices = $this->getClassroomDevices($classroom);
        
        if (!isset($devices[$deviceId])) {
            throw new \InvalidArgumentException('设备不存在');
        }
        
        // 合并配置
        $devices[$deviceId] = array_merge($devices[$deviceId], $config);
        $devices[$deviceId]['updated_at'] = new \DateTime();
        
        // 验证更新后的配置
        $this->validateDeviceConfig($devices[$deviceId]);
        
        $classroom->setDevices($devices);
        $this->entityManager->flush();
        
        $this->logger->info('设备配置更新成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceId,
        ]);
        
        return $devices[$deviceId];
    }

    public function checkDeviceStatus(Classroom $classroom, ?string $deviceId = null): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $results = [];
        
        if ($deviceId !== null) {
            if (!isset($devices[$deviceId])) {
                throw new \InvalidArgumentException('设备不存在');
            }
            $devices = [$deviceId => $devices[$deviceId]];
        }
        
        foreach ($devices as $id => $device) {
            $results[$id] = $this->getDeviceStatus($device);
        }
        
        return $results;
    }

    public function testDeviceConnection(array $deviceConfig): array
    {
        try {
            $type = $deviceConfig['type'] ?? 'unknown';
            
            switch ($type) {
                case 'face_recognition':
                    return $this->testFaceRecognitionDevice($deviceConfig);
                case 'fingerprint':
                    return $this->testFingerprintDevice($deviceConfig);
                case 'card_reader':
                    return $this->testCardReaderDevice($deviceConfig);
                case 'camera':
                    return $this->testCameraDevice($deviceConfig);
                case 'environment_sensor':
                    return $this->testEnvironmentSensor($deviceConfig);
                default:
                    return $this->testGenericDevice($deviceConfig);
            }
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => new \DateTime(),
            ];
        }
    }

    public function getSupportedAttendanceMethods(Classroom $classroom): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $methods = [];
        
        foreach ($devices as $device) {
            $type = $device['type'] ?? 'unknown';
            
            switch ($type) {
                case 'face_recognition':
                    $methods[] = AttendanceMethod::FACE;
                    break;
                case 'fingerprint':
                    $methods[] = AttendanceMethod::FINGERPRINT;
                    break;
                case 'card_reader':
                    $methods[] = AttendanceMethod::CARD;
                    break;
                case 'qr_scanner':
                    $methods[] = AttendanceMethod::QR_CODE;
                    break;
            }
        }
        
        // 总是支持手动考勤
        $methods[] = AttendanceMethod::MANUAL;
        
        // 去重：使用枚举值作为键来去重
        $uniqueMethods = [];
        foreach ($methods as $method) {
            $uniqueMethods[$method->value] = $method;
        }
        
        return array_values($uniqueMethods);
    }

    public function performAttendanceVerification(Classroom $classroom, AttendanceMethod $method, array $data): array
    {
        $devices = $this->getClassroomDevices($classroom);
        
        // 查找支持该考勤方式的设备
        $targetDevice = null;
        foreach ($devices as $device) {
            if ($this->deviceSupportsMethod($device, $method)) {
                $targetDevice = $device;
                break;
            }
        }
        
        if (!$targetDevice && $method !== AttendanceMethod::MANUAL) {
            return [
                'success' => false,
                'result' => VerificationResult::DEVICE_ERROR,
                'message' => '未找到支持该考勤方式的设备',
            ];
        }
        
        try {
            switch ($method) {
                case AttendanceMethod::FACE:
                    return $this->verifyFaceRecognition($targetDevice, $data);
                case AttendanceMethod::FINGERPRINT:
                    return $this->verifyFingerprint($targetDevice, $data);
                case AttendanceMethod::CARD:
                    return $this->verifyCard($targetDevice, $data);
                case AttendanceMethod::QR_CODE:
                    return $this->verifyQrCode($targetDevice, $data);
                case AttendanceMethod::MANUAL:
                    return $this->verifyManual($data);
                default:
                    return [
                        'success' => false,
                        'result' => VerificationResult::FAILED,
                        'message' => '不支持的考勤方式',
                    ];
            }
        } catch (\Throwable $e) {
            $this->logger->error('考勤验证失败', [
                'classroom_id' => $classroom->getId(),
                'method' => $method->value,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'result' => VerificationResult::DEVICE_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getRecordings(Classroom $classroom, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $recordings = [];
        
        foreach ($devices as $device) {
            if ($device['type'] === 'camera' && isset($device['recording_enabled']) && $device['recording_enabled']) {
                $deviceRecordings = $this->getDeviceRecordings($device, $startTime, $endTime);
                $recordings = array_merge($recordings, $deviceRecordings);
            }
        }
        
        return $recordings;
    }

    public function startRecording(Classroom $classroom, array $options = []): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $results = [];
        
        foreach ($devices as $deviceId => $device) {
            if ($device['type'] === 'camera') {
                $results[$deviceId] = $this->startDeviceRecording($device, $options);
            }
        }
        
        return $results;
    }

    public function stopRecording(Classroom $classroom): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $results = [];
        
        foreach ($devices as $deviceId => $device) {
            if ($device['type'] === 'camera') {
                $results[$deviceId] = $this->stopDeviceRecording($device);
            }
        }
        
        return $results;
    }

    public function getEnvironmentData(Classroom $classroom, array $sensors = []): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $data = [];
        
        foreach ($devices as $device) {
            if ($device['type'] === 'environment_sensor') {
                $sensorData = $this->getDeviceEnvironmentData($device, $sensors);
                $data = array_merge($data, $sensorData);
            }
        }
        
        return $data;
    }

    public function setEnvironmentThresholds(Classroom $classroom, array $thresholds): bool
    {
        $devices = $this->getClassroomDevices($classroom);
        $success = true;
        
        foreach ($devices as &$device) {
            if ($device['type'] === 'environment_sensor') {
                $device['thresholds'] = $thresholds;
                $device['thresholds_updated_at'] = new \DateTime();
            }
        }
        
        $classroom->setDevices($devices);
        $this->entityManager->flush();
        
        return $success;
    }

    public function getDeviceLogs(Classroom $classroom, ?string $deviceId = null, ?\DateTimeInterface $startTime = null, ?\DateTimeInterface $endTime = null): array
    {
        // 这里应该从设备日志系统获取数据
        // 目前返回模拟数据
        return [
            'logs' => [
                [
                    'timestamp' => new \DateTime('-1 hour'),
                    'device_id' => $deviceId ?? 'device_001',
                    'level' => 'info',
                    'message' => '设备正常运行',
                ],
                [
                    'timestamp' => new \DateTime('-2 hours'),
                    'device_id' => $deviceId ?? 'device_001',
                    'level' => 'warning',
                    'message' => '网络连接不稳定',
                ],
            ],
            'total' => 2,
        ];
    }

    public function syncDeviceData(Classroom $classroom, array $options = []): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $results = [
            'total' => count($devices),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];
        
        foreach ($devices as $deviceId => $device) {
            try {
                $this->syncSingleDevice($device, $options);
                $results['success']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'device_id' => $deviceId,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }

    /**
     * 验证设备配置
     */
    private function validateDeviceConfig(array $config): void
    {
        if (empty($config['type'])) {
            throw new \InvalidArgumentException('设备类型不能为空');
        }
        
        if (empty($config['name'])) {
            throw new \InvalidArgumentException('设备名称不能为空');
        }
    }

    /**
     * 获取设备状态
     */
    private function getDeviceStatus(array $device): array
    {
        // 这里应该实际检查设备状态
        // 目前返回模拟状态
        return [
            'online' => true,
            'last_heartbeat' => new \DateTime(),
            'health' => 'good',
            'uptime' => '24h 15m',
        ];
    }

    /**
     * 测试人脸识别设备
     */
    private function testFaceRecognitionDevice(array $config): array
    {
        // 模拟人脸识别设备测试
        return [
            'success' => true,
            'message' => '人脸识别设备连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 测试指纹设备
     */
    private function testFingerprintDevice(array $config): array
    {
        // 模拟指纹设备测试
        return [
            'success' => true,
            'message' => '指纹设备连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 测试刷卡设备
     */
    private function testCardReaderDevice(array $config): array
    {
        // 模拟刷卡设备测试
        return [
            'success' => true,
            'message' => '刷卡设备连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 测试摄像头设备
     */
    private function testCameraDevice(array $config): array
    {
        // 模拟摄像头测试
        return [
            'success' => true,
            'message' => '摄像头连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 测试环境传感器
     */
    private function testEnvironmentSensor(array $config): array
    {
        // 模拟环境传感器测试
        return [
            'success' => true,
            'message' => '环境传感器连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 测试通用设备
     */
    private function testGenericDevice(array $config): array
    {
        // 模拟通用设备测试
        return [
            'success' => true,
            'message' => '设备连接正常',
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 检查设备是否支持指定考勤方式
     */
    private function deviceSupportsMethod(array $device, AttendanceMethod $method): bool
    {
        $type = $device['type'] ?? 'unknown';
        
        return match ($method) {
            AttendanceMethod::FACE => $type === 'face_recognition',
            AttendanceMethod::FINGERPRINT => $type === 'fingerprint',
            AttendanceMethod::CARD => $type === 'card_reader',
            AttendanceMethod::QR_CODE => $type === 'qr_scanner',
            AttendanceMethod::MANUAL => true,
            default => false,
        };
    }

    /**
     * 人脸识别验证
     */
    private function verifyFaceRecognition(array $device, array $data): array
    {
        // 模拟人脸识别验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'confidence' => 0.95,
            'user_id' => $data['user_id'] ?? null,
        ];
    }

    /**
     * 指纹验证
     */
    private function verifyFingerprint(array $device, array $data): array
    {
        // 模拟指纹验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'user_id' => $data['user_id'] ?? null,
        ];
    }

    /**
     * 刷卡验证
     */
    private function verifyCard(array $device, array $data): array
    {
        // 模拟刷卡验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'card_id' => $data['card_id'] ?? null,
        ];
    }

    /**
     * 二维码验证
     */
    private function verifyQrCode(array $device, array $data): array
    {
        // 模拟二维码验证
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'qr_code' => $data['qr_code'] ?? null,
        ];
    }

    /**
     * 手动验证
     */
    private function verifyManual(array $data): array
    {
        // 手动验证总是成功
        return [
            'success' => true,
            'result' => VerificationResult::SUCCESS,
            'user_id' => $data['user_id'] ?? null,
        ];
    }

    /**
     * 获取设备录像
     */
    private function getDeviceRecordings(array $device, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        // 模拟获取录像数据
        return [
            [
                'id' => 'recording_001',
                'device_id' => $device['id'],
                'start_time' => $startTime,
                'end_time' => $endTime,
                'file_path' => '/recordings/2025/01/27/recording_001.mp4',
                'file_size' => 1024 * 1024 * 100, // 100MB
            ],
        ];
    }

    /**
     * 开始设备录像
     */
    private function startDeviceRecording(array $device, array $options): array
    {
        // 模拟开始录像
        return [
            'success' => true,
            'recording_id' => 'recording_' . uniqid(),
            'started_at' => new \DateTime(),
        ];
    }

    /**
     * 停止设备录像
     */
    private function stopDeviceRecording(array $device): array
    {
        // 模拟停止录像
        return [
            'success' => true,
            'stopped_at' => new \DateTime(),
        ];
    }

    /**
     * 获取设备环境数据
     */
    private function getDeviceEnvironmentData(array $device, array $sensors): array
    {
        // 模拟环境数据
        return [
            'temperature' => 22.5,
            'humidity' => 45.0,
            'pm25' => 15,
            'co2' => 400,
            'timestamp' => new \DateTime(),
        ];
    }

    /**
     * 同步单个设备数据
     */
    private function syncSingleDevice(array $device, array $options): void
    {
        // 模拟设备数据同步
        $this->logger->info('设备数据同步完成', [
            'device_id' => $device['id'],
            'device_type' => $device['type'],
        ]);
    }
} 