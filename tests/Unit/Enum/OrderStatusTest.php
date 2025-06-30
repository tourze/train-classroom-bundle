<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;

class OrderStatusTest extends TestCase
{
    public function testEnumExists(): void
    {
        $this->assertTrue(enum_exists(OrderStatus::class));
    }

    public function testEnumCases(): void
    {
        $cases = OrderStatus::cases();
        $this->assertCount(4, $cases);
        $this->assertContainsOnlyInstancesOf(OrderStatus::class, $cases);
    }
}