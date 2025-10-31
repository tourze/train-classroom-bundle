<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\ClassroomDataPopulator;

/**
 * @internal
 */
#[CoversClass(ClassroomDataPopulator::class)]
final class ClassroomDataPopulatorTest extends TestCase
{
    public function testPopulateBasicData(): void
    {
        self::markTestIncomplete('Test implementation pending');
    }
}
