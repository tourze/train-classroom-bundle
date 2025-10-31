<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\DeviceSyncManager;

/**
 * @internal
 */
#[CoversClass(DeviceSyncManager::class)]
final class DeviceSyncManagerTest extends TestCase
{
    public function testSyncDevices(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
