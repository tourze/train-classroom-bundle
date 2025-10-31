<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service\DeviceTester;

/**
 * 设备测试器接口
 */
interface DeviceTesterInterface
{
    /**
     * 测试设备连接
     *
     * @param array<string, mixed> $deviceConfig
     * @return array<string, mixed>
     */
    public function test(array $deviceConfig): array;

    /**
     * 判断是否支持该设备类型
     */
    public function supports(string $deviceType): bool;
}
