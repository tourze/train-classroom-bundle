<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\ClassroomUsageStatsCalculator;

/**
 * @internal
 */
#[CoversClass(ClassroomUsageStatsCalculator::class)]
#[RunTestsInSeparateProcesses]
final class ClassroomUsageStatsCalculatorTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Service测试需要容器环境
    }

    public function testServiceExists(): void
    {
        $service = self::getService(ClassroomUsageStatsCalculator::class);
        $this->assertInstanceOf(ClassroomUsageStatsCalculator::class, $service);
    }

    public function testCalculateStats(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
