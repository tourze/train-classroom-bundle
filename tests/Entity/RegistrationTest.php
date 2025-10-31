<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;

/**
 * Registration实体测试类
 *
 * 测试报名实体的基本功能，避免外部依赖
 *
 * @internal
 */
#[CoversClass(Registration::class)]
final class RegistrationTest extends AbstractEntityTestCase
{
    protected function createEntity(): Registration
    {
        return new Registration();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'trainType_online' => ['trainType', TrainType::ONLINE];
        yield 'trainType_null' => ['trainType', null];
        yield 'status' => ['status', OrderStatus::PAID];
        yield 'beginTime' => ['beginTime', new \DateTime('2025-01-15')];
        yield 'endTime_datetime' => ['endTime', new \DateTime('2025-03-15')];
        yield 'endTime_null' => ['endTime', null];
        yield 'firstLearnTime_datetime' => ['firstLearnTime', new \DateTime('2025-01-20 10:00:00')];
        yield 'firstLearnTime_null' => ['firstLearnTime', null];
        yield 'lastLearnTime_datetime' => ['lastLearnTime', new \DateTime('2025-03-10 15:30:00')];
        yield 'lastLearnTime_null' => ['lastLearnTime', null];
        yield 'payTime_datetime' => ['payTime', new \DateTime('2025-01-10 14:20:00')];
        yield 'payTime_null' => ['payTime', null];
        yield 'refundTime_datetime' => ['refundTime', new \DateTime('2025-02-01 11:15:00')];
        yield 'refundTime_null' => ['refundTime', null];
        yield 'payPrice_string' => ['payPrice', '199.00'];
        yield 'payPrice_null' => ['payPrice', null];
        yield 'finished_true' => ['finished', true];
        yield 'finished_false' => ['finished', false];
        yield 'finishTime_datetime' => ['finishTime', new \DateTime('2025-03-15 18:00:00')];
        yield 'finishTime_null' => ['finishTime', null];
        yield 'expired_true' => ['expired', true];
        yield 'expired_false' => ['expired', false];
        yield 'age_int' => ['age', 25];
        yield 'age_null' => ['age', null];
    }

    /**
     * 测试TrainType的设置和获取
     */
    public function testTrainTypeProperty(): void
    {
        $registration = $this->createEntity();
        $trainType = TrainType::ONLINE;
        $registration->setTrainType($trainType);

        $this->assertSame($trainType, $registration->getTrainType());
    }

    /**
     * 测试Status的设置和获取
     */
    public function testStatusProperty(): void
    {
        $registration = $this->createEntity();
        $status = OrderStatus::PAID;
        $registration->setStatus($status);

        $this->assertSame($status, $registration->getStatus());
    }

    /**
     * 测试Status默认值
     */
    public function testStatusDefaultValue(): void
    {
        $registration = $this->createEntity();
        $this->assertSame(OrderStatus::PENDING, $registration->getStatus());
    }

    /**
     * 测试BeginTime的设置和获取
     */
    public function testBeginTimeProperty(): void
    {
        $registration = $this->createEntity();
        $beginTime = new \DateTime('2025-01-15');
        $registration->setBeginTime($beginTime);

        $this->assertSame($beginTime, $registration->getBeginTime());
    }

    /**
     * 测试EndTime的设置和获取
     */
    public function testEndTimeProperty(): void
    {
        $registration = $this->createEntity();
        $endTime = new \DateTime('2025-03-15');
        $registration->setEndTime($endTime);

        $this->assertSame($endTime, $registration->getEndTime());
    }

    /**
     * 测试FirstLearnTime的设置和获取
     */
    public function testFirstLearnTimeProperty(): void
    {
        $registration = $this->createEntity();
        $firstLearnTime = new \DateTime('2025-01-20 10:00:00');
        $registration->setFirstLearnTime($firstLearnTime);

        $this->assertSame($firstLearnTime, $registration->getFirstLearnTime());
    }

    /**
     * 测试LastLearnTime的设置和获取
     */
    public function testLastLearnTimeProperty(): void
    {
        $registration = $this->createEntity();
        $lastLearnTime = new \DateTime('2025-03-10 15:30:00');
        $registration->setLastLearnTime($lastLearnTime);

        $this->assertSame($lastLearnTime, $registration->getLastLearnTime());
    }

    /**
     * 测试PayTime的设置和获取
     */
    public function testPayTimeProperty(): void
    {
        $registration = $this->createEntity();
        $payTime = new \DateTime('2025-01-10 14:20:00');
        $registration->setPayTime($payTime);

        $this->assertSame($payTime, $registration->getPayTime());
    }

    /**
     * 测试RefundTime的设置和获取
     */
    public function testRefundTimeProperty(): void
    {
        $registration = $this->createEntity();
        $refundTime = new \DateTime('2025-02-01 11:15:00');
        $registration->setRefundTime($refundTime);

        $this->assertSame($refundTime, $registration->getRefundTime());
    }

    /**
     * 测试PayPrice的设置和获取
     */
    public function testPayPriceProperty(): void
    {
        $registration = $this->createEntity();
        $payPrice = '199.00';
        $registration->setPayPrice($payPrice);

        $this->assertSame($payPrice, $registration->getPayPrice());
    }

    /**
     * 测试Finished的设置和获取
     */
    public function testFinishedProperty(): void
    {
        $registration = $this->createEntity();
        $registration->setFinished(true);
        $this->assertTrue($registration->isFinished());

        $registration->setFinished(false);
        $this->assertFalse($registration->isFinished());
    }

    /**
     * 测试FinishTime的设置和获取
     */
    public function testFinishTimeProperty(): void
    {
        $registration = $this->createEntity();
        $finishTime = new \DateTime('2025-03-15 18:00:00');
        $registration->setFinishTime($finishTime);

        $this->assertSame($finishTime, $registration->getFinishTime());
    }

    /**
     * 测试Expired的设置和获取
     */
    public function testExpiredProperty(): void
    {
        $registration = $this->createEntity();
        $registration->setExpired(true);
        $this->assertTrue($registration->isExpired());

        $registration->setExpired(false);
        $this->assertFalse($registration->isExpired());
    }

    /**
     * 测试Age的设置和获取
     */
    public function testAgeProperty(): void
    {
        $registration = $this->createEntity();
        $age = 25;
        $registration->setAge($age);

        $this->assertSame($age, $registration->getAge());
    }

    /**
     * 测试AttendanceRecords集合的初始化
     */
    public function testAttendanceRecordsCollectionInitialization(): void
    {
        $registration = $this->createEntity();
        $attendanceRecords = $registration->getAttendanceRecords();

        $this->assertCount(0, $attendanceRecords);
    }

    /**
     * 测试setter方法功能
     */
    public function testSetterMethods(): void
    {
        $registration = $this->createEntity();
        $trainType = TrainType::OFFLINE;
        $status = OrderStatus::PAID;
        $payPrice = '299.00';

        $registration->setTrainType($trainType);
        $registration->setStatus($status);
        $registration->setPayPrice($payPrice);
        $registration->setFinished(true);

        $this->assertSame($trainType, $registration->getTrainType());
        $this->assertSame($status, $registration->getStatus());
        $this->assertSame($payPrice, $registration->getPayPrice());
        $this->assertTrue($registration->isFinished());
    }

    /**
     * 测试所有可选属性的默认值
     */
    public function testOptionalPropertiesDefaultValues(): void
    {
        $registration = $this->createEntity();
        $this->assertNull($registration->getEndTime());
        $this->assertNull($registration->getFirstLearnTime());
        $this->assertNull($registration->getLastLearnTime());
        $this->assertNull($registration->getPayTime());
        $this->assertNull($registration->getRefundTime());
        $this->assertNull($registration->getFinishTime());
        $this->assertNull($registration->getPayPrice());
        $this->assertNull($registration->getAge());
        $this->assertFalse($registration->isFinished());
        $this->assertFalse($registration->isExpired());
    }

    /**
     * 测试边界值情况
     */
    public function testBoundaryValues(): void
    {
        $registration = $this->createEntity();

        // 测试TrainType可以为null
        $registration->setTrainType(null);
        $this->assertNull($registration->getTrainType());

        // 测试TrainType混合模式
        $registration->setTrainType(TrainType::HYBRID);
        $this->assertSame(TrainType::HYBRID, $registration->getTrainType());

        // 测试负价格
        $registration->setPayPrice('-100.0');
        $this->assertSame('-100.0', $registration->getPayPrice());

        // 测试零价格
        $registration->setPayPrice('0.0');
        $this->assertSame('0.0', $registration->getPayPrice());

        // 测试负年龄
        $registration->setAge(-1);
        $this->assertSame(-1, $registration->getAge());

        // 测试零年龄
        $registration->setAge(0);
        $this->assertSame(0, $registration->getAge());
    }
}
