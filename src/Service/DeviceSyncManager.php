<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;

/**
 * 设备同步管理器
 *
 * 负责设备数据的同步
 */
#[WithMonologChannel(channel: 'train_classroom')]
class DeviceSyncManager
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $devices
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function syncDevices(array $devices, array $options = []): array
    {
        $results = $this->initializeResults(\count($devices));

        foreach ($devices as $deviceId => $device) {
            $results = $this->processSingleDevice($deviceId, $device, $options, $results);
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    private function initializeResults(int $total): array
    {
        return [
            'total' => $total,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }

    /**
     * @param int|string $deviceId
     * @param mixed $device
     * @param array<string, mixed> $options
     * @param array<string, mixed> $results
     * @return array<string, mixed>
     */
    private function processSingleDevice($deviceId, $device, array $options, array $results): array
    {
        if (!is_array($device)) {
            return $this->recordError($results, $deviceId, '设备数据格式错误');
        }

        try {
            /** @var array<string, mixed> $device */
            $this->syncSingleDevice($device, $options);
            $successCount = is_int($results['success']) ? $results['success'] : 0;
            $results['success'] = $successCount + 1;
        } catch (\Throwable $e) {
            $results = $this->recordError($results, $deviceId, $e->getMessage());
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $results
     * @param int|string $deviceId
     * @return array<string, mixed>
     */
    private function recordError(array $results, $deviceId, string $error): array
    {
        $failedCount = is_int($results['failed']) ? $results['failed'] : 0;
        $results['failed'] = $failedCount + 1;

        if (!is_array($results['errors'])) {
            $results['errors'] = [];
        }

        $results['errors'][] = [
            'device_id' => $deviceId,
            'error' => $error,
        ];

        return $results;
    }

    /**
     * @param array<string, mixed> $device
     * @param array<string, mixed> $options
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
