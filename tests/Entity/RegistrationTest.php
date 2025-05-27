<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;

/**
 * Registration实体测试类
 * 
 * 测试报名实体的基本功能，避免外部依赖
 */
class RegistrationTest extends TestCase
{
    private Registration $registration;

    protected function setUp(): void
    {
        $this->registration = new Registration();
    }

    /**
     * 测试TrainType的设置和获取
     */
    public function test_train_type_property(): void
    {
        $trainType = TrainType::ONLINE;
        $this->registration->setTrainType($trainType);
        
        $this->assertSame($trainType, $this->registration->getTrainType());
    }

    /**
     * 测试Status的设置和获取
     */
    public function test_status_property(): void
    {
        $status = OrderStatus::PAID;
        $this->registration->setStatus($status);
        
        $this->assertSame($status, $this->registration->getStatus());
    }

    /**
     * 测试Status的默认值
     */
    public function test_status_default_value(): void
    {
        $this->assertSame(OrderStatus::PENDING, $this->registration->getStatus());
    }

    /**
     * 测试BeginTime的设置和获取
     */
    public function test_begin_time_property(): void
    {
        $beginTime = new \DateTime('2025-01-15 09:00:00');
        $this->registration->setBeginTime($beginTime);
        
        $this->assertSame($beginTime, $this->registration->getBeginTime());
    }

    /**
     * 测试EndTime的设置和获取
     */
    public function test_end_time_property(): void
    {
        $endTime = new \DateTime('2025-03-15 17:00:00');
        $this->registration->setEndTime($endTime);
        
        $this->assertSame($endTime, $this->registration->getEndTime());
    }

    /**
     * 测试FirstLearnTime的设置和获取
     */
    public function test_first_learn_time_property(): void
    {
        $firstLearnTime = new \DateTime('2025-01-16 10:00:00');
        $this->registration->setFirstLearnTime($firstLearnTime);
        
        $this->assertSame($firstLearnTime, $this->registration->getFirstLearnTime());
    }

    /**
     * 测试LastLearnTime的设置和获取
     */
    public function test_last_learn_time_property(): void
    {
        $lastLearnTime = new \DateTime('2025-03-14 16:00:00');
        $this->registration->setLastLearnTime($lastLearnTime);
        
        $this->assertSame($lastLearnTime, $this->registration->getLastLearnTime());
    }

    /**
     * 测试PayTime的设置和获取
     */
    public function test_pay_time_property(): void
    {
        $payTime = new \DateTime('2025-01-10 14:30:00');
        $this->registration->setPayTime($payTime);
        
        $this->assertSame($payTime, $this->registration->getPayTime());
    }

    /**
     * 测试RefundTime的设置和获取
     */
    public function test_refund_time_property(): void
    {
        $refundTime = new \DateTime('2025-01-20 11:00:00');
        $this->registration->setRefundTime($refundTime);
        
        $this->assertSame($refundTime, $this->registration->getRefundTime());
    }

    /**
     * 测试PayPrice的设置和获取
     */
    public function test_pay_price_property(): void
    {
        $payPrice = '299.99';
        $this->registration->setPayPrice($payPrice);
        
        $this->assertSame($payPrice, $this->registration->getPayPrice());
    }

    /**
     * 测试Finished的设置和获取
     */
    public function test_finished_property(): void
    {
        $this->registration->setFinished(true);
        $this->assertTrue($this->registration->isFinished());
        
        $this->registration->setFinished(false);
        $this->assertFalse($this->registration->isFinished());
    }

    /**
     * 测试FinishTime的设置和获取
     */
    public function test_finish_time_property(): void
    {
        $finishTime = new \DateTime('2025-03-15 17:00:00');
        $this->registration->setFinishTime($finishTime);
        
        $this->assertSame($finishTime, $this->registration->getFinishTime());
    }

    /**
     * 测试Expired的设置和获取
     */
    public function test_expired_property(): void
    {
        $this->registration->setExpired(true);
        $this->assertTrue($this->registration->isExpired());
        
        $this->registration->setExpired(false);
        $this->assertFalse($this->registration->isExpired());
    }

    /**
     * 测试Age的设置和获取
     */
    public function test_age_property(): void
    {
        $age = 35;
        $this->registration->setAge($age);
        
        $this->assertSame($age, $this->registration->getAge());
    }

    /**
     * 测试AttendanceRecords集合的初始化
     */
    public function test_attendance_records_collection_initialization(): void
    {
        $attendanceRecords = $this->registration->getAttendanceRecords();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $attendanceRecords);
        $this->assertCount(0, $attendanceRecords);
    }

    /**
     * 测试方法链式调用
     */
    public function test_method_chaining(): void
    {
        $trainType = TrainType::OFFLINE;
        $status = OrderStatus::PAID;
        $payPrice = '199.99';
        
        $result = $this->registration
            ->setTrainType($trainType)
            ->setStatus($status)
            ->setPayPrice($payPrice)
            ->setFinished(true);
        
        $this->assertSame($this->registration, $result);
        $this->assertSame($trainType, $this->registration->getTrainType());
        $this->assertSame($status, $this->registration->getStatus());
        $this->assertSame($payPrice, $this->registration->getPayPrice());
        $this->assertTrue($this->registration->isFinished());
    }

    /**
     * 测试可选属性的默认值
     */
    public function test_optional_properties_default_values(): void
    {
        // beginTime是必需的，不测试null值
        $this->assertNull($this->registration->getEndTime());
        $this->assertNull($this->registration->getFirstLearnTime());
        $this->assertNull($this->registration->getLastLearnTime());
        $this->assertNull($this->registration->getPayTime());
        $this->assertNull($this->registration->getRefundTime());
        $this->assertNull($this->registration->getFinishTime());
        $this->assertNull($this->registration->getPayPrice());
        $this->assertNull($this->registration->getAge());
        $this->assertFalse($this->registration->isFinished());
        $this->assertFalse($this->registration->isExpired());
    }

    /**
     * 测试边界值
     */
    public function test_boundary_values(): void
    {
        // 测试null值
        $this->registration->setTrainType(null);
        $this->assertNull($this->registration->getTrainType());
        
        // 测试不同枚举值
        $this->registration->setTrainType(TrainType::HYBRID);
        $this->assertSame(TrainType::HYBRID, $this->registration->getTrainType());
        
        // 测试负价格
        $this->registration->setPayPrice('-100.0');
        $this->assertSame('-100.0', $this->registration->getPayPrice());
        
        // 测试零价格
        $this->registration->setPayPrice('0.0');
        $this->assertSame('0.0', $this->registration->getPayPrice());
        
        // 测试负年龄
        $this->registration->setAge(-1);
        $this->assertSame(-1, $this->registration->getAge());
        
        // 测试零年龄
        $this->registration->setAge(0);
        $this->assertSame(0, $this->registration->getAge());
    }
} 