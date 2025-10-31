<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;

/**
 * @internal
 */
#[CoversClass(OrderStatus::class)]
final class OrderStatusTest extends AbstractEnumTestCase
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

    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = OrderStatus::PENDING->toArray();

        $expectedResult = [
            'value' => 'pending',
            'label' => '待支付',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('pending', $result['value']);
        $this->assertEquals('待支付', $result['label']);
    }

    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = OrderStatus::PENDING->toSelectItem();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('pending', $result['value']);
        $this->assertEquals('待支付', $result['label']);
    }
}
