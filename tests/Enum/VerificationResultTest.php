<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * VerificationResult枚举测试类
 */
class VerificationResultTest extends TestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('SUCCESS', VerificationResult::SUCCESS->value);
        $this->assertEquals('FAILED', VerificationResult::FAILED->value);
        $this->assertEquals('PENDING', VerificationResult::PENDING->value);
        $this->assertEquals('TIMEOUT', VerificationResult::TIMEOUT->value);
        $this->assertEquals('ERROR', VerificationResult::ERROR->value);
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function test_cases_returns_all_enum_values(): void
    {
        $cases = VerificationResult::cases();
        
        $this->assertCount(6, $cases);
        $this->assertContains(VerificationResult::SUCCESS, $cases);
        $this->assertContains(VerificationResult::FAILED, $cases);
        $this->assertContains(VerificationResult::PENDING, $cases);
        $this->assertContains(VerificationResult::TIMEOUT, $cases);
        $this->assertContains(VerificationResult::ERROR, $cases);
        $this->assertContains(VerificationResult::DEVICE_ERROR, $cases);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function test_getLabel_returns_correct_chinese_description(): void
    {
        $this->assertEquals('验证成功', VerificationResult::SUCCESS->getLabel());
        $this->assertEquals('验证失败', VerificationResult::FAILED->getLabel());
        $this->assertEquals('待验证', VerificationResult::PENDING->getLabel());
        $this->assertEquals('验证超时', VerificationResult::TIMEOUT->getLabel());
        $this->assertEquals('验证错误', VerificationResult::ERROR->getLabel());
        $this->assertEquals('设备错误', VerificationResult::DEVICE_ERROR->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function test_getOptions_returns_correct_options_array(): void
    {
        $options = VerificationResult::getOptions();
        
        $expectedOptions = [
            'SUCCESS' => '验证成功',
            'FAILED' => '验证失败',
            'PENDING' => '待验证',
            'TIMEOUT' => '验证超时',
            'ERROR' => '验证错误',
            'DEVICE_ERROR' => '设备错误',
        ];
        
        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(6, $options);
        
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
     * 测试isSuccess方法正确识别成功状态
     */
    public function test_isSuccess_correctly_identifies_success_status(): void
    {
        $this->assertTrue(VerificationResult::SUCCESS->isSuccess());
        $this->assertFalse(VerificationResult::FAILED->isSuccess());
        $this->assertFalse(VerificationResult::PENDING->isSuccess());
        $this->assertFalse(VerificationResult::TIMEOUT->isSuccess());
        $this->assertFalse(VerificationResult::ERROR->isSuccess());
    }

    /**
     * 测试isFailure方法正确识别失败状态
     */
    public function test_isFailure_correctly_identifies_failure_status(): void
    {
        $this->assertTrue(VerificationResult::FAILED->isFailure());
        $this->assertTrue(VerificationResult::TIMEOUT->isFailure());
        $this->assertTrue(VerificationResult::ERROR->isFailure());
        $this->assertTrue(VerificationResult::DEVICE_ERROR->isFailure());
        $this->assertFalse(VerificationResult::SUCCESS->isFailure());
        $this->assertFalse(VerificationResult::PENDING->isFailure());
    }

    /**
     * 测试isPending方法正确识别待处理状态
     */
    public function test_isPending_correctly_identifies_pending_status(): void
    {
        $this->assertTrue(VerificationResult::PENDING->isPending());
        $this->assertFalse(VerificationResult::SUCCESS->isPending());
        $this->assertFalse(VerificationResult::FAILED->isPending());
        $this->assertFalse(VerificationResult::TIMEOUT->isPending());
        $this->assertFalse(VerificationResult::ERROR->isPending());
        $this->assertFalse(VerificationResult::DEVICE_ERROR->isPending());
    }

    /**
     * 测试getColorClass方法返回正确的颜色类名
     */
    public function test_getColorClass_returns_correct_color_classes(): void
    {
        $this->assertEquals('text-success', VerificationResult::SUCCESS->getColorClass());
        $this->assertEquals('text-danger', VerificationResult::FAILED->getColorClass());
        $this->assertEquals('text-warning', VerificationResult::PENDING->getColorClass());
        $this->assertEquals('text-secondary', VerificationResult::TIMEOUT->getColorClass());
        $this->assertEquals('text-danger', VerificationResult::ERROR->getColorClass());
        $this->assertEquals('text-danger', VerificationResult::DEVICE_ERROR->getColorClass());
    }

    /**
     * 测试getIconClass方法返回正确的图标类名
     */
    public function test_getIconClass_returns_correct_icon_classes(): void
    {
        $this->assertEquals('fa-check-circle', VerificationResult::SUCCESS->getIconClass());
        $this->assertEquals('fa-times-circle', VerificationResult::FAILED->getIconClass());
        $this->assertEquals('fa-clock', VerificationResult::PENDING->getIconClass());
        $this->assertEquals('fa-hourglass-end', VerificationResult::TIMEOUT->getIconClass());
        $this->assertEquals('fa-exclamation-triangle', VerificationResult::ERROR->getIconClass());
        $this->assertEquals('fa-cog', VerificationResult::DEVICE_ERROR->getIconClass());
    }

    /**
     * 测试状态的互斥性
     */
    public function test_status_methods_are_mutually_exclusive(): void
    {
        foreach (VerificationResult::cases() as $result) {
            $statusCount = 0;
            if ($result->isSuccess()) $statusCount++;
            if ($result->isFailure()) $statusCount++;
            if ($result->isPending()) $statusCount++;
            
            $this->assertEquals(1, $statusCount, 
                "验证结果 {$result->value} 应该只属于一种状态");
        }
    }

    /**
     * 测试颜色类名都是有效的字符串
     */
    public function test_color_classes_are_valid_strings(): void
    {
        foreach (VerificationResult::cases() as $result) {
            $colorClass = $result->getColorClass();
            $this->assertIsString($colorClass);
            $this->assertNotEmpty($colorClass);
            $this->assertStringStartsWith('text-', $colorClass);
        }
    }

    /**
     * 测试图标类名都是有效的字符串
     */
    public function test_icon_classes_are_valid_strings(): void
    {
        foreach (VerificationResult::cases() as $result) {
            $iconClass = $result->getIconClass();
            $this->assertIsString($iconClass);
            $this->assertNotEmpty($iconClass);
            $this->assertStringStartsWith('fa-', $iconClass);
        }
    }

    /**
     * 测试失败状态都有危险颜色
     */
    public function test_failure_statuses_have_danger_or_secondary_color(): void
    {
        foreach (VerificationResult::cases() as $result) {
            if ($result->isFailure()) {
                $colorClass = $result->getColorClass();
                $this->assertContains($colorClass, ['text-danger', 'text-secondary'],
                    "失败状态 {$result->value} 应该使用危险或次要颜色");
            }
        }
    }

    /**
     * 测试成功状态有成功颜色
     */
    public function test_success_status_has_success_color(): void
    {
        foreach (VerificationResult::cases() as $result) {
            if ($result->isSuccess()) {
                $this->assertEquals('text-success', $result->getColorClass(),
                    "成功状态应该使用成功颜色");
            }
        }
    }

    /**
     * 测试待处理状态有警告颜色
     */
    public function test_pending_status_has_warning_color(): void
    {
        foreach (VerificationResult::cases() as $result) {
            if ($result->isPending()) {
                $this->assertEquals('text-warning', $result->getColorClass(),
                    "待处理状态应该使用警告颜色");
            }
        }
    }

    /**
     * 测试枚举值的字符串表示
     */
    public function test_enum_string_representation(): void
    {
        $this->assertEquals('SUCCESS', (string) VerificationResult::SUCCESS->value);
        $this->assertEquals('FAILED', (string) VerificationResult::FAILED->value);
        $this->assertEquals('PENDING', (string) VerificationResult::PENDING->value);
        $this->assertEquals('TIMEOUT', (string) VerificationResult::TIMEOUT->value);
        $this->assertEquals('ERROR', (string) VerificationResult::ERROR->value);
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function test_enum_from_string(): void
    {
        $this->assertEquals(VerificationResult::SUCCESS, VerificationResult::from('SUCCESS'));
        $this->assertEquals(VerificationResult::FAILED, VerificationResult::from('FAILED'));
        $this->assertEquals(VerificationResult::PENDING, VerificationResult::from('PENDING'));
        $this->assertEquals(VerificationResult::TIMEOUT, VerificationResult::from('TIMEOUT'));
        $this->assertEquals(VerificationResult::ERROR, VerificationResult::from('ERROR'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function test_tryFrom_handles_invalid_values(): void
    {
        $this->assertNull(VerificationResult::tryFrom('INVALID_RESULT'));
        $this->assertNull(VerificationResult::tryFrom(''));
        $this->assertNull(VerificationResult::tryFrom('success')); // 大小写敏感
        $this->assertNull(VerificationResult::tryFrom('Success')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function test_from_throws_exception_for_invalid_values(): void
    {
        $this->expectException(\ValueError::class);
        VerificationResult::from('INVALID_RESULT');
    }

    /**
     * 测试枚举值的比较
     */
    public function test_enum_comparison(): void
    {
        $success1 = VerificationResult::SUCCESS;
        $success2 = VerificationResult::SUCCESS;
        $failed = VerificationResult::FAILED;
        
        $this->assertTrue($success1 === $success2);
        $this->assertFalse($success1 === $failed);
        $this->assertTrue($success1 !== $failed);
    }

    /**
     * 测试所有结果都有唯一的图标类名
     */
    public function test_all_results_have_unique_icon_classes(): void
    {
        $iconClasses = [];
        foreach (VerificationResult::cases() as $result) {
            $iconClass = $result->getIconClass();
            $this->assertNotContains($iconClass, $iconClasses, 
                "图标类名 {$iconClass} 不应该重复");
            $iconClasses[] = $iconClass;
        }
        
        $this->assertCount(6, $iconClasses);
    }
} 