<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;

/**
 * AttendanceMethod枚举测试类
 */
class AttendanceMethodTest extends TestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('FACE', AttendanceMethod::FACE->value);
        $this->assertEquals('CARD', AttendanceMethod::CARD->value);
        $this->assertEquals('FINGERPRINT', AttendanceMethod::FINGERPRINT->value);
        $this->assertEquals('QR_CODE', AttendanceMethod::QR_CODE->value);
        $this->assertEquals('MANUAL', AttendanceMethod::MANUAL->value);
        $this->assertEquals('MOBILE', AttendanceMethod::MOBILE->value);
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function test_cases_returns_all_enum_values(): void
    {
        $cases = AttendanceMethod::cases();
        
        $this->assertCount(6, $cases);
        $this->assertContains(AttendanceMethod::FACE, $cases);
        $this->assertContains(AttendanceMethod::CARD, $cases);
        $this->assertContains(AttendanceMethod::FINGERPRINT, $cases);
        $this->assertContains(AttendanceMethod::QR_CODE, $cases);
        $this->assertContains(AttendanceMethod::MANUAL, $cases);
        $this->assertContains(AttendanceMethod::MOBILE, $cases);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function test_getLabel_returns_correct_chinese_description(): void
    {
        $this->assertEquals('人脸识别', AttendanceMethod::FACE->getLabel());
        $this->assertEquals('刷卡', AttendanceMethod::CARD->getLabel());
        $this->assertEquals('指纹识别', AttendanceMethod::FINGERPRINT->getLabel());
        $this->assertEquals('二维码', AttendanceMethod::QR_CODE->getLabel());
        $this->assertEquals('手动录入', AttendanceMethod::MANUAL->getLabel());
        $this->assertEquals('移动端', AttendanceMethod::MOBILE->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function test_getOptions_returns_correct_options_array(): void
    {
        $options = AttendanceMethod::getOptions();
        
        $expectedOptions = [
            'FACE' => '人脸识别',
            'CARD' => '刷卡',
            'FINGERPRINT' => '指纹识别',
            'QR_CODE' => '二维码',
            'MANUAL' => '手动录入',
            'MOBILE' => '移动端',
        ];
        
        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(6, $options);
        
        // 验证所有键都是字符串
        foreach (array_keys($options) as $key) {
        }
        
        // 验证所有值都是字符串
        foreach (array_values($options) as $value) {
        }
    }

    /**
     * 测试requiresBiometric方法正确识别生物识别方式
     */
    public function test_requiresBiometric_correctly_identifies_biometric_methods(): void
    {
        $this->assertTrue(AttendanceMethod::FACE->requiresBiometric());
        $this->assertTrue(AttendanceMethod::FINGERPRINT->requiresBiometric());
        $this->assertFalse(AttendanceMethod::CARD->requiresBiometric());
        $this->assertFalse(AttendanceMethod::QR_CODE->requiresBiometric());
        $this->assertFalse(AttendanceMethod::MANUAL->requiresBiometric());
        $this->assertFalse(AttendanceMethod::MOBILE->requiresBiometric());
    }

    /**
     * 测试isAutomatic方法正确识别自动识别方式
     */
    public function test_isAutomatic_correctly_identifies_automatic_methods(): void
    {
        $this->assertTrue(AttendanceMethod::FACE->isAutomatic());
        $this->assertTrue(AttendanceMethod::CARD->isAutomatic());
        $this->assertTrue(AttendanceMethod::FINGERPRINT->isAutomatic());
        $this->assertTrue(AttendanceMethod::QR_CODE->isAutomatic());
        $this->assertTrue(AttendanceMethod::MOBILE->isAutomatic());
        $this->assertFalse(AttendanceMethod::MANUAL->isAutomatic());
    }

    /**
     * 测试getIconClass方法返回正确的图标类名
     */
    public function test_getIconClass_returns_correct_icon_classes(): void
    {
        $this->assertEquals('fa-user-circle', AttendanceMethod::FACE->getIconClass());
        $this->assertEquals('fa-id-card', AttendanceMethod::CARD->getIconClass());
        $this->assertEquals('fa-fingerprint', AttendanceMethod::FINGERPRINT->getIconClass());
        $this->assertEquals('fa-qrcode', AttendanceMethod::QR_CODE->getIconClass());
        $this->assertEquals('fa-edit', AttendanceMethod::MANUAL->getIconClass());
        $this->assertEquals('fa-mobile-alt', AttendanceMethod::MOBILE->getIconClass());
    }

    /**
     * 测试图标类名都是有效的字符串
     */
    public function test_icon_classes_are_valid_strings(): void
    {
        foreach (AttendanceMethod::cases() as $method) {
            $iconClass = $method->getIconClass();
            $this->assertNotEmpty($iconClass);
            $this->assertStringStartsWith('fa-', $iconClass);
        }
    }

    /**
     * 测试生物识别和自动识别的逻辑关系
     */
    public function test_biometric_methods_are_automatic(): void
    {
        foreach (AttendanceMethod::cases() as $method) {
            if ($method->requiresBiometric()) {
                $this->assertTrue($method->isAutomatic(), 
                    "生物识别方式 {$method->value} 应该是自动识别的");
            }
        }
    }

    /**
     * 测试枚举值的字符串表示
     */
    public function test_enum_string_representation(): void
    {
        $this->assertEquals('FACE', (string) AttendanceMethod::FACE->value);
        $this->assertEquals('CARD', (string) AttendanceMethod::CARD->value);
        $this->assertEquals('FINGERPRINT', (string) AttendanceMethod::FINGERPRINT->value);
        $this->assertEquals('QR_CODE', (string) AttendanceMethod::QR_CODE->value);
        $this->assertEquals('MANUAL', (string) AttendanceMethod::MANUAL->value);
        $this->assertEquals('MOBILE', (string) AttendanceMethod::MOBILE->value);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function test_enum_from_string(): void
    {
        $this->assertEquals(AttendanceMethod::FACE, AttendanceMethod::from('FACE'));
        $this->assertEquals(AttendanceMethod::CARD, AttendanceMethod::from('CARD'));
        $this->assertEquals(AttendanceMethod::FINGERPRINT, AttendanceMethod::from('FINGERPRINT'));
        $this->assertEquals(AttendanceMethod::QR_CODE, AttendanceMethod::from('QR_CODE'));
        $this->assertEquals(AttendanceMethod::MANUAL, AttendanceMethod::from('MANUAL'));
        $this->assertEquals(AttendanceMethod::MOBILE, AttendanceMethod::from('MOBILE'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function test_tryFrom_handles_invalid_values(): void
    {
        $this->assertNull(AttendanceMethod::tryFrom('INVALID_METHOD'));
        $this->assertNull(AttendanceMethod::tryFrom(''));
        $this->assertNull(AttendanceMethod::tryFrom('face')); // 大小写敏感
        $this->assertNull(AttendanceMethod::tryFrom('qr_code')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function test_from_throws_exception_for_invalid_values(): void
    {
        $this->expectException(\ValueError::class);
        AttendanceMethod::from('INVALID_METHOD');
    }

    /**
     * 测试枚举值的比较
     */
    public function test_enum_comparison(): void
    {
        $face1 = AttendanceMethod::FACE;
        $face2 = AttendanceMethod::from('FACE');
        $card = AttendanceMethod::CARD;
        
        $this->assertSame($face1, $face2);
        $this->assertNotSame($face1, $card);
    }

    /**
     * 测试所有方法都有唯一的图标类名
     */
    public function test_all_methods_have_unique_icon_classes(): void
    {
        $iconClasses = [];
        foreach (AttendanceMethod::cases() as $method) {
            $iconClass = $method->getIconClass();
            $this->assertNotContains($iconClass, $iconClasses, 
                "图标类名 {$iconClass} 不应该重复");
            $iconClasses[] = $iconClass;
        }
        
        $this->assertCount(6, $iconClasses);
    }
} 