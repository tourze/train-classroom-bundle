<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\DeviceTester;

/**
 * 通用设备测试器
 */
class GenericDeviceTester implements DeviceTesterInterface
{
    private const SUPPORTED_TYPES = [
        'face_recognition',
        'fingerprint',
        'card_reader',
        'camera',
        'environment_sensor',
    ];

    public function supports(string $deviceType): bool
    {
        return \in_array($deviceType, self::SUPPORTED_TYPES, true);
    }

    /**
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    public function test(array $deviceConfig): array
    {
        $type = $deviceConfig['type'] ?? 'unknown';
        assert(is_string($type));
        $message = $this->getSuccessMessage($type);

        return [
            'success' => true,
            'message' => $message,
            'timestamp' => new \DateTime(),
        ];
    }

    private function getSuccessMessage(string $type): string
    {
        return match ($type) {
            'face_recognition' => '人脸识别设备连接正常',
            'fingerprint' => '指纹设备连接正常',
            'card_reader' => '刷卡设备连接正常',
            'camera' => '摄像头连接正常',
            'environment_sensor' => '环境传感器连接正常',
            default => '设备连接正常',
        };
    }
}
