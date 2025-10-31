<?php

namespace Tourze\TrainClassroomBundle\Tests\Enum;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;

/**
 * ScheduleStatus枚举测试类
 *
 * @internal
 * */
#[CoversClass(ScheduleStatus::class)]
final class ScheduleStatusTest extends AbstractEnumTestCase
{
    /**
     * 测试枚举值的正确性
     */
    public function testEnumValuesAreCorrect(): void
    {
        $this->assertEquals('SCHEDULED', ScheduleStatus::SCHEDULED->value);
        $this->assertEquals('ONGOING', ScheduleStatus::ONGOING->value);
        $this->assertEquals('IN_PROGRESS', ScheduleStatus::IN_PROGRESS->value);
        $this->assertEquals('COMPLETED', ScheduleStatus::COMPLETED->value);
        $this->assertEquals('CANCELLED', ScheduleStatus::CANCELLED->value);
        $this->assertEquals('SUSPENDED', ScheduleStatus::SUSPENDED->value);
        $this->assertEquals('POSTPONED', ScheduleStatus::POSTPONED->value);
    }

    /**
     * 测试getDescription方法返回正确的中文描述
     */
    public function testGetDescriptionReturnsCorrectChineseDescription(): void
    {
        $this->assertEquals('已排课', ScheduleStatus::SCHEDULED->getDescription());
        $this->assertEquals('进行中', ScheduleStatus::ONGOING->getDescription());
        $this->assertEquals('进行中', ScheduleStatus::IN_PROGRESS->getDescription());
        $this->assertEquals('已完成', ScheduleStatus::COMPLETED->getDescription());
        $this->assertEquals('已取消', ScheduleStatus::CANCELLED->getDescription());
        $this->assertEquals('已暂停', ScheduleStatus::SUSPENDED->getDescription());
        $this->assertEquals('已延期', ScheduleStatus::POSTPONED->getDescription());
    }

    /**
     * 测试getColor方法返回正确的颜色
     */
    public function testGetColorReturnsCorrectColors(): void
    {
        $this->assertEquals('primary', ScheduleStatus::SCHEDULED->getColor());
        $this->assertEquals('success', ScheduleStatus::ONGOING->getColor());
        $this->assertEquals('success', ScheduleStatus::IN_PROGRESS->getColor());
        $this->assertEquals('info', ScheduleStatus::COMPLETED->getColor());
        $this->assertEquals('danger', ScheduleStatus::CANCELLED->getColor());
        $this->assertEquals('warning', ScheduleStatus::SUSPENDED->getColor());
        $this->assertEquals('secondary', ScheduleStatus::POSTPONED->getColor());
    }

    /**
     * 测试getOptions方法返回正确的选项数组
     */
    public function testGetOptionsReturnsCorrectOptionsArray(): void
    {
        $options = ScheduleStatus::getOptions();

        $expectedOptions = [
            'SCHEDULED' => '已排课',
            'ONGOING' => '进行中',
            'COMPLETED' => '已完成',
            'CANCELLED' => '已取消',
            'SUSPENDED' => '已暂停',
            'POSTPONED' => '已延期',
            'IN_PROGRESS' => '进行中',
        ];

        $this->assertEquals($expectedOptions, $options);
        $this->assertCount(7, $options);
    }

    /**
     * 测试isActive方法正确识别活跃状态
     */
    public function testIsActiveCorrectlyIdentifiesActiveStatuses(): void
    {
        $this->assertTrue(ScheduleStatus::SCHEDULED->isActive());
        $this->assertTrue(ScheduleStatus::ONGOING->isActive());
        $this->assertTrue(ScheduleStatus::IN_PROGRESS->isActive());
        $this->assertFalse(ScheduleStatus::COMPLETED->isActive());
        $this->assertFalse(ScheduleStatus::CANCELLED->isActive());
        $this->assertFalse(ScheduleStatus::SUSPENDED->isActive());
        $this->assertFalse(ScheduleStatus::POSTPONED->isActive());
    }

    /**
     * 测试isFinished方法正确识别结束状态
     */
    public function testIsFinishedCorrectlyIdentifiesFinishedStatuses(): void
    {
        $this->assertTrue(ScheduleStatus::COMPLETED->isFinished());
        $this->assertTrue(ScheduleStatus::CANCELLED->isFinished());
        $this->assertFalse(ScheduleStatus::SCHEDULED->isFinished());
        $this->assertFalse(ScheduleStatus::ONGOING->isFinished());
        $this->assertFalse(ScheduleStatus::SUSPENDED->isFinished());
        $this->assertFalse(ScheduleStatus::POSTPONED->isFinished());
    }

    /**
     * 测试isEditable方法正确识别可编辑状态
     */
    public function testIsEditableCorrectlyIdentifiesEditableStatuses(): void
    {
        $this->assertTrue(ScheduleStatus::SCHEDULED->isEditable());
        $this->assertTrue(ScheduleStatus::SUSPENDED->isEditable());
        $this->assertTrue(ScheduleStatus::POSTPONED->isEditable());
        $this->assertFalse(ScheduleStatus::ONGOING->isEditable());
        $this->assertFalse(ScheduleStatus::IN_PROGRESS->isEditable());
        $this->assertFalse(ScheduleStatus::COMPLETED->isEditable());
        $this->assertFalse(ScheduleStatus::CANCELLED->isEditable());
    }

    /**
     * 测试状态的互斥性
     */
    public function testStatusMethodsAreMutuallyExclusive(): void
    {
        foreach (ScheduleStatus::cases() as $status) {
            $activeCount = $status->isActive() ? 1 : 0;
            $finishedCount = $status->isFinished() ? 1 : 0;

            // 一个状态不能同时是活跃和结束的
            $this->assertLessThanOrEqual(
                1,
                $activeCount + $finishedCount,
                "状态 {$status->value} 不能同时是活跃和结束状态"
            );
        }
    }

    /**
     * 测试进行中状态的特殊性
     */
    public function testOngoingStatusIsActiveButNotEditable(): void
    {
        $ongoing = ScheduleStatus::ONGOING;
        $this->assertTrue($ongoing->isActive());
        $this->assertFalse($ongoing->isEditable());
        $this->assertFalse($ongoing->isFinished());
    }

    /**
     * 测试完成状态的特殊性
     */
    public function testCompletedStatusIsFinishedButNotActive(): void
    {
        $completed = ScheduleStatus::COMPLETED;
        $this->assertTrue($completed->isFinished());
        $this->assertFalse($completed->isActive());
        $this->assertFalse($completed->isEditable());
    }

    /**
     * 测试从字符串创建枚举实例
     */
    public function testEnumFromString(): void
    {
        $this->assertEquals(ScheduleStatus::SCHEDULED, ScheduleStatus::from('SCHEDULED'));
        $this->assertEquals(ScheduleStatus::ONGOING, ScheduleStatus::from('ONGOING'));
        $this->assertEquals(ScheduleStatus::IN_PROGRESS, ScheduleStatus::from('IN_PROGRESS'));
        $this->assertEquals(ScheduleStatus::COMPLETED, ScheduleStatus::from('COMPLETED'));
        $this->assertEquals(ScheduleStatus::CANCELLED, ScheduleStatus::from('CANCELLED'));
        $this->assertEquals(ScheduleStatus::SUSPENDED, ScheduleStatus::from('SUSPENDED'));
        $this->assertEquals(ScheduleStatus::POSTPONED, ScheduleStatus::from('POSTPONED'));
    }

    /**
     * 测试tryFrom方法处理无效值
     */
    public function testTryFromHandlesInvalidValues(): void
    {
        $this->assertNull(ScheduleStatus::tryFrom('INVALID_STATUS'));
        $this->assertNull(ScheduleStatus::tryFrom(''));
        $this->assertNull(ScheduleStatus::tryFrom('scheduled')); // 大小写敏感
    }

    /**
     * 测试from方法抛出异常处理无效值
     */
    public function testFromThrowsExceptionForInvalidValues(): void
    {
        $this->expectException(\ValueError::class);
        ScheduleStatus::from('INVALID_STATUS');
    }

    /**
     * 测试枚举值的比较
     */
    public function testEnumComparison(): void
    {
        $scheduled1 = ScheduleStatus::SCHEDULED;
        $scheduled2 = ScheduleStatus::from('SCHEDULED');
        $ongoing = ScheduleStatus::ONGOING;

        $this->assertSame($scheduled1, $scheduled2);
        $this->assertNotEquals($scheduled1->value, $ongoing->value);
    }

    public function testToArrayReturnsCorrectArrayFormat(): void
    {
        $result = ScheduleStatus::SCHEDULED->toArray();

        $expectedResult = [
            'value' => 'SCHEDULED',
            'label' => '已排课',
        ];

        $this->assertEquals($expectedResult, $result);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('SCHEDULED', $result['value']);
        $this->assertEquals('已排课', $result['label']);
    }

    public function testToSelectItemReturnsCorrectSelectItemFormat(): void
    {
        $result = ScheduleStatus::SCHEDULED->toSelectItem();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals('SCHEDULED', $result['value']);
        $this->assertEquals('已排课', $result['label']);
    }
}
