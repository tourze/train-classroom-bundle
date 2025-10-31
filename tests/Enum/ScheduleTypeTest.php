<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * ScheduleType枚举测试类
 *
 * @internal
 * */
#[CoversClass(ScheduleType::class)]
final class ScheduleTypeTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function testEnumValuesAreCorrect(): void
    {
        $this->assertEquals('REGULAR', ScheduleType::REGULAR->value);
        $this->assertEquals('MAKEUP', ScheduleType::MAKEUP->value);
        $this->assertEquals('EXAM', ScheduleType::EXAM->value);
        $this->assertEquals('MEETING', ScheduleType::MEETING->value);
        $this->assertEquals('PRACTICE', ScheduleType::PRACTICE->value);
        $this->assertEquals('LECTURE', ScheduleType::LECTURE->value);
    }

    /**
     * 测试getDescription方法返回正确的中文描述
     */
    public function testGetDescriptionReturnsCorrectChineseDescription(): void
    {
        $this->assertEquals('常规课程', ScheduleType::REGULAR->getDescription());
        $this->assertEquals('补课', ScheduleType::MAKEUP->getDescription());
        $this->assertEquals('考试', ScheduleType::EXAM->getDescription());
        $this->assertEquals('会议', ScheduleType::MEETING->getDescription());
        $this->assertEquals('实训', ScheduleType::PRACTICE->getDescription());
        $this->assertEquals('讲座', ScheduleType::LECTURE->getDescription());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function testGetOptionsReturnsCorrectOptionsArray(): void
    {
        $options = ScheduleType::getOptions();

        $expectedOptions = [
            'REGULAR' => '常规课程',
            'MAKEUP' => '补课',
            'EXAM' => '考试',
            'MEETING' => '会议',
            'PRACTICE' => '实训',
            'LECTURE' => '讲座',
        ];

        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(6, $options);
    }

    /**
     * 测试isTeaching方法正确识别教学类型
     */
    public function testIsTeachingCorrectlyIdentifiesTeachingTypes(): void
    {
        $this->assertTrue(ScheduleType::REGULAR->isTeaching());
        $this->assertTrue(ScheduleType::MAKEUP->isTeaching());
        $this->assertTrue(ScheduleType::PRACTICE->isTeaching());
        $this->assertTrue(ScheduleType::LECTURE->isTeaching());
        $this->assertFalse(ScheduleType::EXAM->isTeaching());
        $this->assertFalse(ScheduleType::MEETING->isTeaching());
    }

    /**
     * 测试isAssessment方法正确识别评估类型
     */
    public function testIsAssessmentCorrectlyIdentifiesAssessmentTypes(): void
    {
        $this->assertTrue(ScheduleType::EXAM->isAssessment());
        $this->assertFalse(ScheduleType::REGULAR->isAssessment());
        $this->assertFalse(ScheduleType::MAKEUP->isAssessment());
        $this->assertFalse(ScheduleType::MEETING->isAssessment());
        $this->assertFalse(ScheduleType::PRACTICE->isAssessment());
        $this->assertFalse(ScheduleType::LECTURE->isAssessment());
    }

    /**
     * 测试教学类型和评估类型的互斥性
     */
    public function testTeachingAndAssessmentAreMutuallyExclusive(): void
    {
        foreach (ScheduleType::cases() as $type) {
            // 一个类型不能同时是教学和评估类型
            $this->assertNotEquals(
                $type->isTeaching() && $type->isAssessment(),
                true,
                "类型 {$type->value} 不能同时是教学和评估类型"
            );
        }
    }

    /**
     * 测试所有类型都有分类
     */
    public function testAllTypesHaveClassification(): void
    {
        foreach (ScheduleType::cases() as $type) {
            $hasClassification = $type->isTeaching() || $type->isAssessment()
                                || ScheduleType::MEETING === $type;
            $this->assertTrue(
                $hasClassification,
                "类型 {$type->value} 应该有明确的分类"
            );
        }
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function testEnumFromString(): void
    {
        $this->assertEquals(ScheduleType::REGULAR, ScheduleType::from('REGULAR'));
        $this->assertEquals(ScheduleType::MAKEUP, ScheduleType::from('MAKEUP'));
        $this->assertEquals(ScheduleType::EXAM, ScheduleType::from('EXAM'));
        $this->assertEquals(ScheduleType::MEETING, ScheduleType::from('MEETING'));
        $this->assertEquals(ScheduleType::PRACTICE, ScheduleType::from('PRACTICE'));
        $this->assertEquals(ScheduleType::LECTURE, ScheduleType::from('LECTURE'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromHandlesInvalidValues(): void
    {
        $this->assertNull(ScheduleType::tryFrom('INVALID_TYPE'));
        $this->assertNull(ScheduleType::tryFrom(''));
        $this->assertNull(ScheduleType::tryFrom('regular')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function testFromThrowsExceptionForInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        ScheduleType::from('INVALID_TYPE');
    }

    /**
     * 测试枚举值的比较
     */
    public function testEnumComparison(): void
    {
        $regular1 = ScheduleType::REGULAR;
        $regular2 = ScheduleType::from('REGULAR');
        $exam = ScheduleType::EXAM;

        $this->assertSame($regular1, $regular2);
        $this->assertNotEquals($regular1->value, $exam->value);
    }

    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ScheduleType::REGULAR->toArray();

        $expectedResult = [
            'value' => 'REGULAR',
            'label' => '常规课程',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('REGULAR', $result['value']);
        $this->assertEquals('常规课程', $result['label']);
    }

    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = ScheduleType::REGULAR->toSelectItem();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('REGULAR', $result['value']);
        $this->assertEquals('常规课程', $result['label']);
    }
}
