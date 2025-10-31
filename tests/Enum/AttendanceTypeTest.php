<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;

/**
 * AttendanceType枚举测试类
 *
 * @internal
 * */
#[CoversClass(AttendanceType::class)]
final class AttendanceTypeTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function testEnumValuesAreCorrect(): void
    {
        $this->assertEquals('SIGN_IN', AttendanceType::SIGN_IN->value);
        $this->assertEquals('SIGN_OUT', AttendanceType::SIGN_OUT->value);
        $this->assertEquals('BREAK_OUT', AttendanceType::BREAK_OUT->value);
        $this->assertEquals('BREAK_IN', AttendanceType::BREAK_IN->value);
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function testCasesReturnsAllEnumValues(): void
    {
        $cases = AttendanceType::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(AttendanceType::SIGN_IN, $cases);
        $this->assertContains(AttendanceType::SIGN_OUT, $cases);
        $this->assertContains(AttendanceType::BREAK_OUT, $cases);
        $this->assertContains(AttendanceType::BREAK_IN, $cases);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function testGetLabelReturnsCorrectChineseDescription(): void
    {
        $this->assertEquals('签到', AttendanceType::SIGN_IN->getLabel());
        $this->assertEquals('签退', AttendanceType::SIGN_OUT->getLabel());
        $this->assertEquals('休息外出', AttendanceType::BREAK_OUT->getLabel());
        $this->assertEquals('休息返回', AttendanceType::BREAK_IN->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function testGetOptionsReturnsCorrectOptionsArray(): void
    {
        $options = AttendanceType::getOptions();

        $expectedOptions = [
            'SIGN_IN' => '签到',
            'SIGN_OUT' => '签退',
            'BREAK_OUT' => '休息外出',
            'BREAK_IN' => '休息返回',
        ];

        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(4, $options);

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
     * 测试isSignIn方法正确识别签到类型
     */
    public function testIsSignInCorrectlyIdentifiesSignInTypes(): void
    {
        $this->assertTrue(AttendanceType::SIGN_IN->isSignIn());
        $this->assertTrue(AttendanceType::BREAK_IN->isSignIn());
        $this->assertFalse(AttendanceType::SIGN_OUT->isSignIn());
        $this->assertFalse(AttendanceType::BREAK_OUT->isSignIn());
    }

    /**
     * 测试isSignOut方法正确识别签退类型
     */
    public function testIsSignOutCorrectlyIdentifiesSignOutTypes(): void
    {
        $this->assertTrue(AttendanceType::SIGN_OUT->isSignOut());
        $this->assertTrue(AttendanceType::BREAK_OUT->isSignOut());
        $this->assertFalse(AttendanceType::SIGN_IN->isSignOut());
        $this->assertFalse(AttendanceType::BREAK_IN->isSignOut());
    }

    /**
     * 测试枚举值的互斥性
     */
    public function testEnumValuesAreMutuallyExclusive(): void
    {
        foreach (AttendanceType::cases() as $type) {
            // 每个类型要么是签到，要么是签退，不能同时是两者
            $this->assertNotEquals($type->isSignIn(), $type->isSignOut());
        }
    }

    /**
     * 测试枚举值的字符串表示
     */
    public function testEnumStringRepresentation(): void
    {
        $this->assertEquals('SIGN_IN', (string) AttendanceType::SIGN_IN->value);
        $this->assertEquals('SIGN_OUT', (string) AttendanceType::SIGN_OUT->value);
        $this->assertEquals('BREAK_OUT', (string) AttendanceType::BREAK_OUT->value);
        $this->assertEquals('BREAK_IN', (string) AttendanceType::BREAK_IN->value);
    }

    /**
     * 测试枚举值的比较
     */
    public function testEnumComparison(): void
    {
        $signIn1 = AttendanceType::SIGN_IN;
        $signIn2 = AttendanceType::from('SIGN_IN');
        $signOut = AttendanceType::SIGN_OUT;

        $this->assertSame($signIn1, $signIn2);
        $this->assertNotEquals($signIn1->value, $signOut->value);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function testEnumFromString(): void
    {
        $this->assertEquals(AttendanceType::SIGN_IN, AttendanceType::from('SIGN_IN'));
        $this->assertEquals(AttendanceType::SIGN_OUT, AttendanceType::from('SIGN_OUT'));
        $this->assertEquals(AttendanceType::BREAK_OUT, AttendanceType::from('BREAK_OUT'));
        $this->assertEquals(AttendanceType::BREAK_IN, AttendanceType::from('BREAK_IN'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromHandlesInvalidValues(): void
    {
        $this->assertNull(AttendanceType::tryFrom('INVALID_VALUE'));
        $this->assertNull(AttendanceType::tryFrom(''));
        $this->assertNull(AttendanceType::tryFrom('sign_in')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function testFromThrowsExceptionForInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        AttendanceType::from('INVALID_VALUE');
    }

    /**
     * 测试toArray方法返回正确的数组格式
     */
    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = AttendanceType::SIGN_IN->toArray();

        $expectedResult = [
            'value' => 'SIGN_IN',
            'label' => '签到',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('SIGN_IN', $result['value']);
        $this->assertEquals('签到', $result['label']);
    }

    /**
     * 测试toSelectItem方法返回正确的选择项格式
     */
    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = AttendanceType::SIGN_IN->toSelectItem();

        $expectedResult = [
            'label' => '签到',
            'text' => '签到',
            'value' => 'SIGN_IN',
            'name' => '签到',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('SIGN_IN', $result['value']);
        $this->assertEquals('签到', $result['label']);
        $this->assertEquals('签到', $result['text']);
        $this->assertEquals('签到', $result['name']);
    }
}
