<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\TrainType;

class TrainTypeTest extends TestCase
{
    public function testEnumExists(): void
    {
        $this->assertTrue(enum_exists(TrainType::class));
    }

    public function testEnumCases(): void
    {
        $cases = TrainType::cases();
        $this->assertCount(3, $cases);
        $this->assertContainsOnlyInstancesOf(TrainType::class, $cases);
    }
}