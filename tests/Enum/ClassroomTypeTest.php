<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\ClassroomType;

/**
 * ClassroomType枚举测试类
 *
 * @internal
 * */
#[CoversClass(ClassroomType::class)]
final class ClassroomTypeTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function testEnumValuesAreCorrect(): void
    {
        $this->assertEquals('PHYSICAL', ClassroomType::PHYSICAL->value);
        $this->assertEquals('VIRTUAL', ClassroomType::VIRTUAL->value);
        $this->assertEquals('HYBRID', ClassroomType::HYBRID->value);
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function testCasesReturnsAllEnumValues(): void
    {
        $cases = ClassroomType::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ClassroomType::PHYSICAL, $cases);
        $this->assertContains(ClassroomType::VIRTUAL, $cases);
        $this->assertContains(ClassroomType::HYBRID, $cases);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function testGetLabelReturnsCorrectChineseDescription(): void
    {
        $this->assertEquals('物理教室', ClassroomType::PHYSICAL->getLabel());
        $this->assertEquals('虚拟教室', ClassroomType::VIRTUAL->getLabel());
        $this->assertEquals('混合教室', ClassroomType::HYBRID->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function testGetOptionsReturnsCorrectOptionsArray(): void
    {
        $options = ClassroomType::getOptions();

        $expectedOptions = [
            'PHYSICAL' => '物理教室',
            'VIRTUAL' => '虚拟教室',
            'HYBRID' => '混合教室',
        ];

        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(3, $options);

        // 验证所有键都是字符串
        foreach (array_keys($options) as $key) {
            $this->assertIsString($key);
        }

        // 验证所有值都是字符串
        foreach (array_values($options) as $value) {
            $this->assertIsString($value);
        }
    }

    /**
     * 测试requiresPhysicalSpace方法正确识别需要物理空间的类型
     */
    public function testRequiresPhysicalSpaceCorrectlyIdentifiesPhysicalTypes(): void
    {
        $this->assertTrue(ClassroomType::PHYSICAL->requiresPhysicalSpace());
        $this->assertTrue(ClassroomType::HYBRID->requiresPhysicalSpace());
        $this->assertFalse(ClassroomType::VIRTUAL->requiresPhysicalSpace());
    }

    /**
     * 测试supportsOnline方法正确识别支持在线功能的类型
     */
    public function testSupportsOnlineCorrectlyIdentifiesOnlineTypes(): void
    {
        $this->assertTrue(ClassroomType::VIRTUAL->supportsOnline());
        $this->assertTrue(ClassroomType::HYBRID->supportsOnline());
        $this->assertFalse(ClassroomType::PHYSICAL->supportsOnline());
    }

    /**
     * 测试getIconClass方法返回正确的图标类名
     */
    public function testGetIconClassReturnsCorrectIconClasses(): void
    {
        $this->assertEquals('fa-building', ClassroomType::PHYSICAL->getIconClass());
        $this->assertEquals('fa-desktop', ClassroomType::VIRTUAL->getIconClass());
        $this->assertEquals('fa-laptop', ClassroomType::HYBRID->getIconClass());
    }

    /**
     * 测试getDescription方法返回正确的描述信息
     */
    public function testGetDescriptionReturnsCorrectDescriptions(): void
    {
        $this->assertEquals('传统线下培训教室，需要学员到场参与', ClassroomType::PHYSICAL->getDescription());
        $this->assertEquals('在线虚拟教室，支持远程培训', ClassroomType::VIRTUAL->getDescription());
        $this->assertEquals('混合式教室，支持线上线下同时进行', ClassroomType::HYBRID->getDescription());
    }

    /**
     * 测试图标类名都是有效的字符串
     */
    public function testIconClassesAreValidStrings(): void
    {
        foreach (ClassroomType::cases() as $type) {
            $iconClass = $type->getIconClass();
            $this->assertNotEmpty($iconClass);
            $this->assertStringStartsWith('fa-', $iconClass);
        }
    }

    /**
     * 测试描述信息都是有效的字符串
     */
    public function testDescriptionsAreValidStrings(): void
    {
        foreach (ClassroomType::cases() as $type) {
            $description = $type->getDescription();
            $this->assertNotEmpty($description);
        }
    }

    /**
     * 测试混合教室同时支持物理和在线功能
     */
    public function testHybridSupportsBothPhysicalAndOnline(): void
    {
        $hybrid = ClassroomType::HYBRID;
        $this->assertTrue($hybrid->requiresPhysicalSpace());
        $this->assertTrue($hybrid->supportsOnline());
    }

    /**
     * 测试物理教室和虚拟教室的互斥性
     */
    public function testPhysicalAndVirtualAreMutuallyExclusive(): void
    {
        $physical = ClassroomType::PHYSICAL;
        $virtual = ClassroomType::VIRTUAL;

        // 物理教室不支持在线
        $this->assertTrue($physical->requiresPhysicalSpace());
        $this->assertFalse($physical->supportsOnline());

        // 虚拟教室不需要物理空间
        $this->assertFalse($virtual->requiresPhysicalSpace());
        $this->assertTrue($virtual->supportsOnline());
    }

    /**
     * 测试枚举值的字符串表示
     */
    public function testEnumStringRepresentation(): void
    {
        $this->assertEquals('PHYSICAL', (string) ClassroomType::PHYSICAL->value);
        $this->assertEquals('VIRTUAL', (string) ClassroomType::VIRTUAL->value);
        $this->assertEquals('HYBRID', (string) ClassroomType::HYBRID->value);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function testEnumFromString(): void
    {
        $this->assertEquals(ClassroomType::PHYSICAL, ClassroomType::from('PHYSICAL'));
        $this->assertEquals(ClassroomType::VIRTUAL, ClassroomType::from('VIRTUAL'));
        $this->assertEquals(ClassroomType::HYBRID, ClassroomType::from('HYBRID'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromHandlesInvalidValues(): void
    {
        $this->assertNull(ClassroomType::tryFrom('INVALID_TYPE'));
        $this->assertNull(ClassroomType::tryFrom(''));
        $this->assertNull(ClassroomType::tryFrom('physical')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function testFromThrowsExceptionForInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        ClassroomType::from('INVALID_TYPE');
    }

    /**
     * 测试枚举值的比较
     */
    public function testEnumComparison(): void
    {
        $physical1 = ClassroomType::PHYSICAL;
        $physical2 = ClassroomType::from('PHYSICAL');
        $virtual = ClassroomType::VIRTUAL;

        $this->assertSame($physical1, $physical2);
        $this->assertNotEquals($physical1->value, $virtual->value);
    }

    /**
     * 测试所有类型都有唯一的图标类名
     */
    public function testAllTypesHaveUniqueIconClasses(): void
    {
        $iconClasses = [];
        foreach (ClassroomType::cases() as $type) {
            $iconClass = $type->getIconClass();
            $this->assertNotContains(
                $iconClass,
                $iconClasses,
                "图标类名 {$iconClass} 不应该重复"
            );
            $iconClasses[] = $iconClass;
        }

        $this->assertCount(3, $iconClasses);
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ClassroomType::PHYSICAL->toArray();

        $expectedResult = [
            'value' => 'PHYSICAL',
            'label' => '物理教室',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('PHYSICAL', $result['value']);
        $this->assertEquals('物理教室', $result['label']);
    }

    /**
     * 测试toSelectItem方法返回正确的选择项格式
     */
    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = ClassroomType::PHYSICAL->toSelectItem();

        $expectedResult = [
            'label' => '物理教室',
            'text' => '物理教室',
            'value' => 'PHYSICAL',
            'name' => '物理教室',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('PHYSICAL', $result['value']);
        $this->assertEquals('物理教室', $result['label']);
        $this->assertEquals('物理教室', $result['text']);
        $this->assertEquals('物理教室', $result['name']);
    }
}
