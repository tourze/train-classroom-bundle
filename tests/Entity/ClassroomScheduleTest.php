<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * ClassroomSchedule实体测试类
 *
 * @internal
 */
#[CoversClass(ClassroomSchedule::class)]
final class ClassroomScheduleTest extends AbstractEntityTestCase
{
    private Classroom&MockObject $classroom;

    protected function createEntity(): ClassroomSchedule
    {
        return new ClassroomSchedule();
    }

    /**
     * @return array<array{0: string, 1: mixed}>
     */
    public static function propertiesProvider(): array
    {
        return [
            ['teacherId', 'TEACHER_001'],
            ['scheduleDate', new \DateTimeImmutable('2024-01-01')],
            ['startTime', new \DateTimeImmutable('2024-01-01 09:00:00')],
            ['endTime', new \DateTimeImmutable('2024-01-01 10:00:00')],
            ['scheduleType', ScheduleType::REGULAR],
            ['scheduleStatus', ScheduleStatus::SCHEDULED],
            ['scheduleConfig', ['key' => 'value']],
            ['scheduleConfig', null],
            ['courseContent', '课程内容'],
            ['courseContent', null],
            ['expectedStudents', 30],
            ['expectedStudents', null],
            ['actualStudents', 25],
            ['actualStudents', null],
            ['remark', '测试课程'],
            ['remark', null],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * 使用Classroom具体Entity类进行Mock的原因：
         * 1) Classroom是Doctrine实体类，包含复杂的属性和关联关系
         * 2) 测试需要验证ClassroomSchedule与Classroom的关联关系，使用具体类确保类型一致
         * 3) Entity类没有对应的接口，使用具体类是唯一选择
         * 4) 在Entity单元测试中模拟关联实体是常见做法，避免数据库依赖
         */
        $this->classroom = $this->createMock(Classroom::class);
    }

    /**
     * 测试Classroom关联关系的设置和获取
     */
    public function testClassroomRelationship(): void
    {
        $schedule = $this->createEntity();
        $schedule->setClassroom($this->classroom);

        $this->assertSame($this->classroom, $schedule->getClassroom());
    }

    /**
     * 测试TeacherId的设置和获取
     */
    public function testTeacherIdProperty(): void
    {
        $schedule = $this->createEntity();
        $teacherId = 'TEACHER_001';
        $schedule->setTeacherId($teacherId);

        $this->assertSame($teacherId, $schedule->getTeacherId());
    }

    /**
     * 测试ScheduleType的设置和获取
     */
    public function testScheduleTypeProperty(): void
    {
        $schedule = $this->createEntity();
        $scheduleType = ScheduleType::REGULAR;
        $schedule->setScheduleType($scheduleType);

        $this->assertSame($scheduleType, $schedule->getScheduleType());
    }

    /**
     * 测试StartTime的设置和获取
     */
    public function testStartTimeProperty(): void
    {
        $schedule = $this->createEntity();
        $startTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $schedule->setStartTime($startTime);

        $this->assertSame($startTime, $schedule->getStartTime());
    }

    /**
     * 测试EndTime的设置和获取
     */
    public function testEndTimeProperty(): void
    {
        $schedule = $this->createEntity();
        $endTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $schedule->setEndTime($endTime);

        $this->assertSame($endTime, $schedule->getEndTime());
    }

    /**
     * 测试ScheduleStatus的设置和获取
     */
    public function testScheduleStatusProperty(): void
    {
        $schedule = $this->createEntity();
        $scheduleStatus = ScheduleStatus::SCHEDULED;
        $schedule->setScheduleStatus($scheduleStatus);

        $this->assertSame($scheduleStatus, $schedule->getScheduleStatus());
    }

    /**
     * 测试Remark的设置和获取
     */
    public function testRemarkProperty(): void
    {
        $schedule = $this->createEntity();
        $remark = '培训课程';
        $schedule->setRemark($remark);

        $this->assertSame($remark, $schedule->getRemark());
    }

    /**
     * 测试Remark可以为null
     */
    public function testRemarkCanBeNull(): void
    {
        $schedule = $this->createEntity();
        $schedule->setRemark(null);

        $this->assertNull($schedule->getRemark());
    }

    /**
     * 测试toString返回id
     */
    public function testToStringReturnsId(): void
    {
        $schedule = $this->createEntity();
        // 由于ID为null，toString应该返回空字符串
        $this->assertSame('', (string) $schedule);
    }

    /**
     * 测试setter方法功能
     */
    public function testSetterMethods(): void
    {
        $schedule = $this->createEntity();
        $startTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $endTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $scheduleDate = new \DateTimeImmutable('2024-01-01');

        $schedule->setClassroom($this->classroom);
        $schedule->setTeacherId('TEACHER_001');
        $schedule->setScheduleDate($scheduleDate);
        $schedule->setScheduleType(ScheduleType::REGULAR);
        $schedule->setStartTime($startTime);
        $schedule->setEndTime($endTime);
        $schedule->setScheduleStatus(ScheduleStatus::SCHEDULED);
        $schedule->setRemark('测试课程');

        $this->assertSame($this->classroom, $schedule->getClassroom());
        $this->assertSame('TEACHER_001', $schedule->getTeacherId());
        $this->assertSame($scheduleDate, $schedule->getScheduleDate());
        $this->assertSame(ScheduleType::REGULAR, $schedule->getScheduleType());
        $this->assertSame($startTime, $schedule->getStartTime());
        $this->assertSame($endTime, $schedule->getEndTime());
        $this->assertSame(ScheduleStatus::SCHEDULED, $schedule->getScheduleStatus());
        $this->assertSame('测试课程', $schedule->getRemark());
    }

    /**
     * 测试边界值情况
     */
    public function testBoundaryValues(): void
    {
        $schedule = $this->createEntity();

        // 测试空字符串教师ID
        $schedule->setTeacherId('');
        $this->assertSame('', $schedule->getTeacherId());

        // 测试长字符串教师ID
        $longTeacherId = str_repeat('TEACHER_', 20);
        $schedule->setTeacherId($longTeacherId);
        $this->assertSame($longTeacherId, $schedule->getTeacherId());

        // 测试空字符串备注
        $schedule->setRemark('');
        $this->assertSame('', $schedule->getRemark());

        // 测试长字符串备注
        $longRemark = str_repeat('这是一个很长的备注', 100);
        $schedule->setRemark($longRemark);
        $this->assertSame($longRemark, $schedule->getRemark());
    }

    /**
     * 测试时间逻辑验证
     */
    public function testTimeLogic(): void
    {
        $schedule = $this->createEntity();
        $startTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $endTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        $schedule->setStartTime($startTime);
        $schedule->setEndTime($endTime);

        $this->assertSame($startTime, $schedule->getStartTime());
        $this->assertSame($endTime, $schedule->getEndTime());

        // 验证时间顺序
        $this->assertLessThan($endTime, $startTime);
    }
}
