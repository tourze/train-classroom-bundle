<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;
use Tourze\TrainClassroomBundle\Service\DeviceTester\DeviceTesterInterface;

/**
 * 设备配置管理器
 *
 * 负责设备的添加、删除和配置更新
 */
class DeviceConfigManager
{
    /** @param array<DeviceTesterInterface> $testers */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly array $testers,
    ) {
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    public function addDevice(Classroom $classroom, array $deviceConfig, callable $getDevicesCallback): array
    {
        $this->validateDeviceConfig($deviceConfig);
        $preparedConfig = $this->prepareDeviceConfig($deviceConfig);
        $this->verifyDeviceConnection($preparedConfig);

        $devices = $getDevicesCallback($classroom);
        if (!is_array($devices)) {
            $devices = [];
        }
        $deviceIdValue = $preparedConfig['id'];
        $deviceId = is_string($deviceIdValue) || is_int($deviceIdValue) ? (string) $deviceIdValue : 'device_unknown';
        $devices[$deviceId] = $preparedConfig;

        /** @var array<string, mixed> $devices */
        $classroom->setDevices($devices);
        $this->entityManager->flush();

        $this->logDeviceAdded($classroom, $preparedConfig);

        return $preparedConfig;
    }

    public function removeDevice(Classroom $classroom, string $deviceId, callable $getDevicesCallback): bool
    {
        $devices = $getDevicesCallback($classroom);
        if (!is_array($devices)) {
            return false;
        }

        if (!isset($devices[$deviceId])) {
            return false;
        }

        unset($devices[$deviceId]);
        /** @var array<string, mixed> $devices */
        $classroom->setDevices($devices);
        $this->entityManager->flush();

        $this->logger->info('设备移除成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceId,
        ]);

        return true;
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function updateConfig(Classroom $classroom, string $deviceId, array $config, callable $getDevicesCallback): array
    {
        $devices = $getDevicesCallback($classroom);
        if (!is_array($devices)) {
            throw new InvalidArgumentException('设备列表无效');
        }
        /** @var array<string, mixed> $devices */
        $this->ensureDeviceExists($devices, $deviceId);

        $existingConfig = is_array($devices[$deviceId]) ? $devices[$deviceId] : [];
        /** @var array<string, mixed> $existingConfig */
        $updatedConfig = $this->mergeAndValidateConfig($existingConfig, $config);
        $devices[$deviceId] = $updatedConfig;

        /** @var array<string, mixed> $devices */
        $classroom->setDevices($devices);
        $this->entityManager->flush();

        $this->logConfigUpdate($classroom, $deviceId);

        return $updatedConfig;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function validateDeviceConfig(array $config): void
    {
        if (!isset($config['type']) || !is_string($config['type']) || '' === $config['type']) {
            throw new InvalidArgumentException('设备类型不能为空');
        }

        if (!isset($config['name']) || !is_string($config['name']) || '' === $config['name']) {
            throw new InvalidArgumentException('设备名称不能为空');
        }
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    private function prepareDeviceConfig(array $deviceConfig): array
    {
        $deviceId = $deviceConfig['id'] ?? uniqid('device_');
        $deviceConfig['id'] = $deviceId;
        $deviceConfig['added_at'] = new \DateTime();

        return $deviceConfig;
    }

    /**
     * @param array<string, mixed> $deviceConfig
     */
    private function verifyDeviceConnection(array $deviceConfig): void
    {
        $connectionTest = $this->testDeviceConnection($deviceConfig);
        if (!(bool) $connectionTest['success']) {
            $error = is_string($connectionTest['error'] ?? null) ? $connectionTest['error'] : '未知错误';
            throw new RuntimeException('设备连接测试失败: ' . $error);
        }
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    private function testDeviceConnection(array $deviceConfig): array
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
     * @param array<string, mixed> $deviceConfig
     */
    private function logDeviceAdded(Classroom $classroom, array $deviceConfig): void
    {
        $this->logger->info('设备添加成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceConfig['id'],
            'device_type' => $deviceConfig['type'] ?? 'unknown',
        ]);
    }

    /**
     * @param array<string, mixed> $devices
     */
    private function ensureDeviceExists(array $devices, string $deviceId): void
    {
        if (!isset($devices[$deviceId])) {
            throw new InvalidArgumentException('设备不存在');
        }
    }

    /**
     * @param array<string, mixed> $existingConfig
     * @param array<string, mixed> $newConfig
     * @return array<string, mixed>
     */
    private function mergeAndValidateConfig(array $existingConfig, array $newConfig): array
    {
        $merged = array_merge($existingConfig, $newConfig);
        $merged['updated_at'] = new \DateTime();

        $this->validateDeviceConfig($merged);

        return $merged;
    }

    private function logConfigUpdate(Classroom $classroom, string $deviceId): void
    {
        $this->logger->info('设备配置更新成功', [
            'classroom_id' => $classroom->getId(),
            'device_id' => $deviceId,
        ]);
    }
}
