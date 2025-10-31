<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\ClassroomUsageStatsCalculator;

/**
 * @internal
 */
#[CoversClass(ClassroomUsageStatsCalculator::class)]
final class ClassroomUsageStatsCalculatorTest extends TestCase
{
    public function testCalculateStats(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
