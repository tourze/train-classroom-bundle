<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\AttendanceVerifierInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\CardVerifier;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\FaceRecognitionVerifier;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\FingerprintVerifier;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\ManualVerifier;
use Tourze\TrainClassroomBundle\Service\AttendanceVerifier\QrCodeVerifier;
use Tourze\TrainClassroomBundle\Service\DeviceTester\DeviceTesterInterface;
use Tourze\TrainClassroomBundle\Service\DeviceTester\GenericDeviceTester;

/**
 * 设备集成服务实现
 *
 * 提供考勤设备、监控设备等的集成和管理功能
 */
#[WithMonologChannel(channel: 'train_classroom')]
class DeviceService implements DeviceServiceInterface
{
    /** @var array<DeviceTesterInterface> */
    private readonly array $testers;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeviceConfigManager $configManager,
        private readonly AttendanceDeviceManager $attendanceManager,
        private readonly DeviceSyncManager $syncManager,
    ) {
        $this->testers = [
            new GenericDeviceTester(),
        ];
    }

    /** @return array<string, mixed> */
    public function getClassroomDevices(Classroom $classroom): array
    {
        $devices = $classroom->getDevices() ?? [];

        return $this->enrichDevicesWithStatus($devices);
    }

    /**
     * @param array<string, mixed> $devices
     * @return array<string, mixed>
     */
    private function enrichDevicesWithStatus(array $devices): array
    {
        foreach ($devices as $deviceId => $device) {
            if (!is_array($device) || !is_string($deviceId)) {
                continue;
            }

            /** @var array<string, mixed> $device */
            $deviceWithStatus = $device;
            $deviceWithStatus['status'] = $this->getDeviceStatus($device);
            $devices[$deviceId] = $deviceWithStatus;
        }

        return $devices;
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    public function addDevice(Classroom $classroom, array $deviceConfig): array
    {
        return $this->configManager->addDevice($classroom, $deviceConfig, fn (Classroom $c) => $this->getClassroomDevices($c));
    }

    public function removeDevice(Classroom $classroom, string $deviceId): bool
    {
        return $this->configManager->removeDevice($classroom, $deviceId, fn (Classroom $c) => $this->getClassroomDevices($c));
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function updateDeviceConfig(Classroom $classroom, string $deviceId, array $config): array
    {
        return $this->configManager->updateConfig($classroom, $deviceId, $config, fn (Classroom $c) => $this->getClassroomDevices($c));
    }

    /**
     * @return array<string, mixed>
     */
    public function checkDeviceStatus(Classroom $classroom, ?string $deviceId = null): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $devicesToCheck = $this->selectDevicesToCheck($devices, $deviceId);
        $results = [];

        foreach ($devicesToCheck as $id => $device) {
            if (is_array($device)) {
                /** @var array<string, mixed> $device */
                $results[$id] = $this->getDeviceStatus($device);
            }
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $devices
     * @return array<string, mixed>
     */
    private function selectDevicesToCheck(array $devices, ?string $deviceId): array
    {
        if (null === $deviceId) {
            return $devices;
        }

        if (!isset($devices[$deviceId])) {
            throw new InvalidArgumentException('设备不存在');
        }

        return [$deviceId => $devices[$deviceId]];
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    public function testDeviceConnection(array $deviceConfig): array
    {
        try {
            $type = is_string($deviceConfig['type'] ?? null) ? $deviceConfig['type'] : 'unknown';

            foreach ($this->testers as $tester) {
                if ($tester->supports($type)) {
                    return $tester->test($deviceConfig);
                }
            }

            // Fallback to generic test
            return $this->testers[0]->test($deviceConfig);
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => new \DateTime(),
            ];
        }
    }

    /**
     * @return array<int, AttendanceMethod>
     */
    public function getSupportedAttendanceMethods(Classroom $classroom): array
    {
        $devices = $this->getClassroomDevices($classroom);

        return $this->attendanceManager->getSupportedMethods($devices);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function performAttendanceVerification(Classroom $classroom, AttendanceMethod $method, array $data): array
    {
        $devices = $this->getClassroomDevices($classroom);

        return $this->attendanceManager->performVerification($classroom, $devices, $method, $data);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRecordings(Classroom $classroom, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $cameraDevices = $this->filterDevicesByType($devices, 'camera');
        $recordings = [];

        foreach ($cameraDevices as $device) {
            if (isset($device['recording_enabled']) && (bool) $device['recording_enabled']) {
                $deviceRecordings = $this->getDeviceRecordings($device, $startTime, $endTime);
                $recordings = array_merge($recordings, $deviceRecordings);
            }
        }

        return $recordings;
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function startRecording(Classroom $classroom, array $options = []): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $cameraDevices = $this->filterDevicesByTypeWithIds($devices, 'camera');
        $results = [];

        foreach ($cameraDevices as $deviceId => $device) {
            $deviceIdString = is_string($deviceId) ? $deviceId : (string) $deviceId;
            $results[$deviceIdString] = $this->startDeviceRecording($device, $options);
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    public function stopRecording(Classroom $classroom): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $cameraDevices = $this->filterDevicesByTypeWithIds($devices, 'camera');
        $results = [];

        foreach ($cameraDevices as $deviceId => $device) {
            $deviceIdString = is_string($deviceId) ? $deviceId : (string) $deviceId;
            $results[$deviceIdString] = $this->stopDeviceRecording($device);
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $sensors
     * @return array<string, mixed>
     */
    public function getEnvironmentData(Classroom $classroom, array $sensors = []): array
    {
        $devices = $this->getClassroomDevices($classroom);
        $sensorDevices = $this->filterDevicesByType($devices, 'environment_sensor');
        $data = [];

        foreach ($sensorDevices as $device) {
            $sensorData = $this->getDeviceEnvironmentData($device, $sensors);
            $data = array_merge($data, $sensorData);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $thresholds
     */
    public function setEnvironmentThresholds(Classroom $classroom, array $thresholds): void
    {
        $devices = $this->getClassroomDevices($classroom);
        $sensorDevices = $this->filterDevicesByTypeWithIds($devices, 'environment_sensor');

        foreach ($sensorDevices as $deviceId => $device) {
            if (!is_array($device) || !is_string($deviceId)) {
                continue;
            }

            /** @var array<string, mixed> $updatedDevice */
            $updatedDevice = $device;
            $updatedDevice['thresholds'] = $thresholds;
            $updatedDevice['thresholds_updated_at'] = new \DateTime();
            $devices[$deviceId] = $updatedDevice;
        }

        /** @var array<string, mixed> $devices */
        $classroom->setDevices($devices);
        $this->entityManager->flush();
    }

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function syncDeviceData(Classroom $classroom, array $options = []): array
    {
        $devices = $this->getClassroomDevices($classroom);

        return $this->syncManager->syncDevices($devices, $options);
    }

    /**
     * 按类型过滤设备
     *
     * @param array<string, mixed> $devices
     * @return array<int, array<string, mixed>>
     */
    private function filterDevicesByType(array $devices, string $type): array
    {
        $filtered = [];

        foreach ($devices as $device) {
            if (is_array($device) && isset($device['type']) && $device['type'] === $type) {
                /** @var array<string, mixed> $device */
                $filtered[] = $device;
            }
        }

        return $filtered;
    }

    /**
     * 按类型过滤设备（保留ID）
     *
     * @param array<string, mixed> $devices
     * @return array<string|int, array<string, mixed>>
     */
    private function filterDevicesByTypeWithIds(array $devices, string $type): array
    {
        $filtered = [];

        foreach ($devices as $deviceId => $device) {
            if (is_array($device) && isset($device['type']) && $device['type'] === $type) {
                /** @var array<string, mixed> $device */
                $filtered[$deviceId] = $device;
            }
        }

        return $filtered;
    }

    /**
     * 获取设备状态
     */
    /**
     * @param array<string, mixed> $device
     * @return array<string, mixed>
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
     * 获取设备录像
     */
    /**
     * @param array<string, mixed> $device
     * @return array<int, array<string, mixed>>
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
                'file_path' => 'recordings' . DIRECTORY_SEPARATOR . '2025' . DIRECTORY_SEPARATOR . '01' . DIRECTORY_SEPARATOR . '27' . DIRECTORY_SEPARATOR . 'recording_001.mp4',
                'file_size' => 1024 * 1024 * 100, // 100MB
            ],
        ];
    }

    /**
     * 开始设备录像
     */
    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $options
     * @return array<string, mixed>
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
    /**
     * @param array<string, mixed> $device
     * @return array<string, mixed>
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
    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $sensors
     * @return array<string, mixed>
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
}
