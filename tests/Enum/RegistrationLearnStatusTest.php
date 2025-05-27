<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Enum\RegistrationLearnStatus;

/**
 * RegistrationLearnStatus枚举测试类
 */
class RegistrationLearnStatusTest extends TestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function test_enum_values_are_correct(): void
    {
        $this->assertEquals('pending', RegistrationLearnStatus::PENDING->value);
        $this->assertEquals('learning', RegistrationLearnStatus::LEARNING->value);
        $this->assertEquals('finished', RegistrationLearnStatus::FINISHED->value);
    }

    /**
     * 测试getLabel方法返回正确的中文描述
     */
    public function test_getLabel_returns_correct_chinese_description(): void
    {
        $this->assertEquals('未开始', RegistrationLearnStatus::PENDING->getLabel());
        $this->assertEquals('学习中', RegistrationLearnStatus::LEARNING->getLabel());
        $this->assertEquals('已完成', RegistrationLearnStatus::FINISHED->getLabel());
    }

    /**
     * 测试枚举cases方法返回所有枚举值
     */
    public function test_cases_returns_all_enum_values(): void
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
    public function test_enum_from_string(): void
    {
        $this->assertEquals(RegistrationLearnStatus::PENDING, RegistrationLearnStatus::from('pending'));
        $this->assertEquals(RegistrationLearnStatus::LEARNING, RegistrationLearnStatus::from('learning'));
        $this->assertEquals(RegistrationLearnStatus::FINISHED, RegistrationLearnStatus::from('finished'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function test_tryFrom_handles_invalid_values(): void
    {
        $this->assertNull(RegistrationLearnStatus::tryFrom('invalid_status'));
        $this->assertNull(RegistrationLearnStatus::tryFrom(''));
        $this->assertNull(RegistrationLearnStatus::tryFrom('PENDING')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function test_from_throws_exception_for_invalid_values(): void
    {
        $this->expectException(\ValueError::class);
        RegistrationLearnStatus::from('invalid_status');
    }
} 