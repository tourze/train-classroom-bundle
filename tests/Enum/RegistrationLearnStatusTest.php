<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\RegistrationLearnStatus;

/**
 * RegistrationLearnStatus枚举测试类
 *
 * @internal
 * */
#[CoversClass(RegistrationLearnStatus::class)]
final class RegistrationLearnStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function testEnumValuesAreCorrect(): void
    {
        $this->assertEquals('pending', RegistrationLearnStatus::PENDING->value);
        $this->assertEquals('learning', RegistrationLearnStatus::LEARNING->value);
        $this->assertEquals('finished', RegistrationLearnStatus::FINISHED->value);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function testGetLabelReturnsCorrectChineseDescription(): void
    {
        $this->assertEquals('未开始', RegistrationLearnStatus::PENDING->getLabel());
        $this->assertEquals('学习中', RegistrationLearnStatus::LEARNING->getLabel());
        $this->assertEquals('已完成', RegistrationLearnStatus::FINISHED->getLabel());
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function testCasesReturnsAllEnumValues(): void
    {
        $cases = RegistrationLearnStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(RegistrationLearnStatus::PENDING, $cases);
        $this->assertContains(RegistrationLearnStatus::LEARNING, $cases);
        $this->assertContains(RegistrationLearnStatus::FINISHED, $cases);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function testEnumFromString(): void
    {
        $this->assertEquals(RegistrationLearnStatus::PENDING, RegistrationLearnStatus::from('pending'));
        $this->assertEquals(RegistrationLearnStatus::LEARNING, RegistrationLearnStatus::from('learning'));
        $this->assertEquals(RegistrationLearnStatus::FINISHED, RegistrationLearnStatus::from('finished'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromHandlesInvalidValues(): void
    {
        $this->assertNull(RegistrationLearnStatus::tryFrom('invalid_status'));
        $this->assertNull(RegistrationLearnStatus::tryFrom(''));
        $this->assertNull(RegistrationLearnStatus::tryFrom('PENDING')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function testFromThrowsExceptionForInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        RegistrationLearnStatus::from('invalid_status');
    }

    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = RegistrationLearnStatus::PENDING->toArray();

        $expectedResult = [
            'value' => 'pending',
            'label' => '未开始',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('pending', $result['value']);
        $this->assertEquals('未开始', $result['label']);
    }

    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = RegistrationLearnStatus::PENDING->toSelectItem();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('pending', $result['value']);
        $this->assertEquals('未开始', $result['label']);
    }
}
