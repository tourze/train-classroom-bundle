<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\TrainType;

/**
 * @internal
 */
#[CoversClass(TrainType::class)]
final class TrainTypeTest extends AbstractEnumTestCase
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

    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = TrainType::ONLINE->toArray();

        $expectedResult = [
            'value' => 'online',
            'label' => '线上培训',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('online', $result['value']);
        $this->assertEquals('线上培训', $result['label']);
    }

    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = TrainType::ONLINE->toSelectItem();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('online', $result['value']);
        $this->assertEquals('线上培训', $result['label']);
    }
}
