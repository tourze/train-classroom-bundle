<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\ClassroomDataPopulator;

/**
 * @internal
 */
#[CoversClass(ClassroomDataPopulator::class)]
#[RunTestsInSeparateProcesses]
final class ClassroomDataPopulatorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service测试需要容器环境
    }

    public function testServiceExists(): void
    {
        $service = self::getService(ClassroomDataPopulator::class);
        $this->assertInstanceOf(ClassroomDataPopulator::class, $service);
    }

    public function testPopulateBasicData(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
