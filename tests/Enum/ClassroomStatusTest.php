<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\ClassroomStatus;

/**
 * ClassroomStatus枚举测试类
 */
class ClassroomStatusTest extends TestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('ACTIVE', ClassroomStatus::ACTIVE->value);
        $this->assertEquals('INACTIVE', ClassroomStatus::INACTIVE->value);
        $this->assertEquals('MAINTENANCE', ClassroomStatus::MAINTENANCE->value);
        $this->assertEquals('RESERVED', ClassroomStatus::RESERVED->value);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function test_getLabel_returns_correct_chinese_description(): void
    {
        $this->assertEquals('正常使用', ClassroomStatus::ACTIVE->getLabel());
        $this->assertEquals('暂停使用', ClassroomStatus::INACTIVE->getLabel());
        $this->assertEquals('维护中', ClassroomStatus::MAINTENANCE->getLabel());
        $this->assertEquals('预留', ClassroomStatus::RESERVED->getLabel());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function test_getOptions_returns_correct_options_array(): void
    {
        $options = ClassroomStatus::getOptions();
        
        $expectedOptions = [
            'ACTIVE' => '正常使用',
            'INACTIVE' => '暂停使用',
            'MAINTENANCE' => '维护中',
            'RESERVED' => '预留',
        ];
        
        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(4, $options);
    }

    /**
     * 测试isAvailableForTraining方法
     */
    public function test_isAvailableForTraining_correctly_identifies_available_status(): void
    {
        $this->assertTrue(ClassroomStatus::ACTIVE->isAvailableForTraining());
        $this->assertFalse(ClassroomStatus::INACTIVE->isAvailableForTraining());
        $this->assertFalse(ClassroomStatus::MAINTENANCE->isAvailableForTraining());
        $this->assertFalse(ClassroomStatus::RESERVED->isAvailableForTraining());
    }

    /**
     * 测试needsMaintenance方法
     */
    public function test_needsMaintenance_correctly_identifies_maintenance_status(): void
    {
        $this->assertTrue(ClassroomStatus::MAINTENANCE->needsMaintenance());
        $this->assertFalse(ClassroomStatus::ACTIVE->needsMaintenance());
        $this->assertFalse(ClassroomStatus::INACTIVE->needsMaintenance());
        $this->assertFalse(ClassroomStatus::RESERVED->needsMaintenance());
    }

    /**
     * 测试getColorClass方法返回正确的颜色类名
     */
    public function test_getColorClass_returns_correct_color_classes(): void
    {
        $this->assertEquals('text-success', ClassroomStatus::ACTIVE->getColorClass());
        $this->assertEquals('text-secondary', ClassroomStatus::INACTIVE->getColorClass());
        $this->assertEquals('text-warning', ClassroomStatus::MAINTENANCE->getColorClass());
        $this->assertEquals('text-info', ClassroomStatus::RESERVED->getColorClass());
    }

    /**
     * 测试getBadgeClass方法返回正确的徽章类名
     */
    public function test_getBadgeClass_returns_correct_badge_classes(): void
    {
        $this->assertEquals('badge-success', ClassroomStatus::ACTIVE->getBadgeClass());
        $this->assertEquals('badge-secondary', ClassroomStatus::INACTIVE->getBadgeClass());
        $this->assertEquals('badge-warning', ClassroomStatus::MAINTENANCE->getBadgeClass());
        $this->assertEquals('badge-info', ClassroomStatus::RESERVED->getBadgeClass());
    }

    /**
     * 测试getIconClass方法返回正确的图标类名
     */
    public function test_getIconClass_returns_correct_icon_classes(): void
    {
        $this->assertEquals('fa-check-circle', ClassroomStatus::ACTIVE->getIconClass());
        $this->assertEquals('fa-pause-circle', ClassroomStatus::INACTIVE->getIconClass());
        $this->assertEquals('fa-tools', ClassroomStatus::MAINTENANCE->getIconClass());
        $this->assertEquals('fa-bookmark', ClassroomStatus::RESERVED->getIconClass());
    }

    /**
     * 测试状态的互斥性
     */
    public function test_status_methods_are_mutually_exclusive(): void
    {
        foreach (ClassroomStatus::cases() as $status) {
            $availableCount = $status->isAvailableForTraining() ? 1 : 0;
            $maintenanceCount = $status->needsMaintenance() ? 1 : 0;
            
            // 一个状态不能同时是可用和需要维护的
            $this->assertLessThanOrEqual(1, $availableCount + $maintenanceCount);
        }
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function test_enum_from_string(): void
    {
        $this->assertEquals(ClassroomStatus::ACTIVE, ClassroomStatus::from('ACTIVE'));
        $this->assertEquals(ClassroomStatus::INACTIVE, ClassroomStatus::from('INACTIVE'));
        $this->assertEquals(ClassroomStatus::MAINTENANCE, ClassroomStatus::from('MAINTENANCE'));
        $this->assertEquals(ClassroomStatus::RESERVED, ClassroomStatus::from('RESERVED'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function test_tryFrom_handles_invalid_values(): void
    {
        $this->assertNull(ClassroomStatus::tryFrom('INVALID_STATUS'));
        $this->assertNull(ClassroomStatus::tryFrom(''));
        $this->assertNull(ClassroomStatus::tryFrom('active')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function test_from_throws_exception_for_invalid_values(): void
    {
        $this->expectException(\ValueError::class);
        ClassroomStatus::from('INVALID_STATUS');
    }
} 