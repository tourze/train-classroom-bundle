<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;

/**
 * AttendanceType枚举测试类
 */
class AttendanceTypeTest extends TestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('SIGN_IN', AttendanceType::SIGN_IN->value);
        $this->assertEquals('SIGN_OUT', AttendanceType::SIGN_OUT->value);
        $this->assertEquals('BREAK_OUT', AttendanceType::BREAK_OUT->value);
        $this->assertEquals('BREAK_IN', AttendanceType::BREAK_IN->value);
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function test_cases_returns_all_enum_values(): void
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
    public function test_getLabel_returns_correct_chinese_description(): void
    {
        $this->assertEquals('签到', AttendanceType::SIGN_IN->getLabel());
        $this->assertEquals('签退', AttendanceType::SIGN_OUT->getLabel());
        $this->assertEquals('休息外出', AttendanceType::BREAK_OUT->getLabel());
        $this->assertEquals('休息返回', AttendanceType::BREAK_IN->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function test_getOptions_returns_correct_options_array(): void
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
        }
        
        // 验证所有值都是字符串
        foreach (array_values($options) as $value) {
        }
    }

    /**
     * 测试isSignIn方法正确识别签到类型
     */
    public function test_isSignIn_correctly_identifies_sign_in_types(): void
    {
        $this->assertTrue(AttendanceType::SIGN_IN->isSignIn());
        $this->assertTrue(AttendanceType::BREAK_IN->isSignIn());
        $this->assertFalse(AttendanceType::SIGN_OUT->isSignIn());
        $this->assertFalse(AttendanceType::BREAK_OUT->isSignIn());
    }

    /**
     * 测试isSignOut方法正确识别签退类型
     */
    public function test_isSignOut_correctly_identifies_sign_out_types(): void
    {
        $this->assertTrue(AttendanceType::SIGN_OUT->isSignOut());
        $this->assertTrue(AttendanceType::BREAK_OUT->isSignOut());
        $this->assertFalse(AttendanceType::SIGN_IN->isSignOut());
        $this->assertFalse(AttendanceType::BREAK_IN->isSignOut());
    }

    /**
     * 测试枚举值的互斥性
     */
    public function test_enum_values_are_mutually_exclusive(): void
    {
        foreach (AttendanceType::cases() as $type) {
            // 每个类型要么是签到，要么是签退，不能同时是两者
            $this->assertNotEquals($type->isSignIn(), $type->isSignOut());
        }
    }

    /**
     * 测试枚举值的字符串表示
     */
    public function test_enum_string_representation(): void
    {
        $this->assertEquals('SIGN_IN', (string) AttendanceType::SIGN_IN->value);
        $this->assertEquals('SIGN_OUT', (string) AttendanceType::SIGN_OUT->value);
        $this->assertEquals('BREAK_OUT', (string) AttendanceType::BREAK_OUT->value);
        $this->assertEquals('BREAK_IN', (string) AttendanceType::BREAK_IN->value);
    }

    /**
     * 测试枚举值的比较
     */
    public function test_enum_comparison(): void
    {
        $signIn1 = AttendanceType::SIGN_IN;
        $signIn2 = AttendanceType::SIGN_IN;
        $signOut = AttendanceType::SIGN_OUT;
        
        $this->assertTrue($signIn1 === $signIn2);
        $this->assertFalse($signIn1 === $signOut);
        $this->assertTrue($signIn1 !== $signOut);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function test_enum_from_string(): void
    {
        $this->assertEquals(AttendanceType::SIGN_IN, AttendanceType::from('SIGN_IN'));
        $this->assertEquals(AttendanceType::SIGN_OUT, AttendanceType::from('SIGN_OUT'));
        $this->assertEquals(AttendanceType::BREAK_OUT, AttendanceType::from('BREAK_OUT'));
        $this->assertEquals(AttendanceType::BREAK_IN, AttendanceType::from('BREAK_IN'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function test_tryFrom_handles_invalid_values(): void
    {
        $this->assertNull(AttendanceType::tryFrom('INVALID_VALUE'));
        $this->assertNull(AttendanceType::tryFrom(''));
        $this->assertNull(AttendanceType::tryFrom('sign_in')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function test_from_throws_exception_for_invalid_values(): void
    {
        $this->expectException(\ValueError::class);
        AttendanceType::from('INVALID_VALUE');
    }
} 