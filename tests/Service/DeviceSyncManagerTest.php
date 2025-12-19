<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\DeviceSyncManager;

/**
 * @internal
 */
#[CoversClass(DeviceSyncManager::class)]
#[RunTestsInSeparateProcesses]
final class DeviceSyncManagerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service测试需要容器环境
    }

    public function testSyncDevices(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
