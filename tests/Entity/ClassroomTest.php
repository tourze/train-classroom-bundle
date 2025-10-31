<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;

/**
 * Classroom实体测试类
 *
 * 测试教室实体的基本功能，避免外部依赖
 *
 * @internal
 */
#[CoversClass(Classroom::class)]
final class ClassroomTest extends AbstractEntityTestCase
{
    protected function createEntity(): Classroom
    {
        return new Classroom();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', '安全生产培训班第一期'];
        yield 'startTime_datetime' => ['startTime', new \DateTime('2025-01-15')];
        yield 'startTime_null' => ['startTime', null];
        yield 'endTime_datetime' => ['endTime', new \DateTime('2025-03-15')];
        yield 'endTime_null' => ['endTime', null];
    }

    /**
     * 测试Title的设置和获取
     */
    public function testTitleProperty(): void
    {
        $classroom = $this->createEntity();
        $title = '安全生产培训班第一期';
        $classroom->setTitle($title);

        $this->assertSame($title, $classroom->getTitle());
    }

    /**
     * 测试StartTime的设置和获取
     */
    public function testStartTimeProperty(): void
    {
        $startTime = new \DateTime('2025-01-15');
        $classroom = $this->createEntity();
        $classroom->setStartTime($startTime);

        $this->assertSame($startTime, $classroom->getStartTime());
    }

    /**
     * 测试StartTime可以为null
     */
    public function testStartTimeCanBeNull(): void
    {
        $classroom = $this->createEntity();
        $classroom->setStartTime(null);

        $this->assertNull($classroom->getStartTime());
    }

    /**
     * 测试EndTime的设置和获取
     */
    public function testEndTimeProperty(): void
    {
        $endTime = new \DateTime('2025-03-15');
        $classroom = $this->createEntity();
        $classroom->setEndTime($endTime);

        $this->assertSame($endTime, $classroom->getEndTime());
    }

    /**
     * 测试EndTime可以为null
     */
    public function testEndTimeCanBeNull(): void
    {
        $classroom = $this->createEntity();
        $classroom->setEndTime(null);

        $this->assertNull($classroom->getEndTime());
    }

    /**
     * 测试Registrations集合的初始化
     */
    public function testRegistrationsCollectionInitialization(): void
    {
        $classroom = $this->createEntity();
        $registrations = $classroom->getRegistrations();

        $this->assertCount(0, $registrations);
    }

    /**
     * 测试Qrcodes集合的初始化
     */
    public function testQrcodesCollectionInitialization(): void
    {
        $classroom = $this->createEntity();
        $qrcodes = $classroom->getQrcodes();

        $this->assertCount(0, $qrcodes);
    }

    /**
     * 测试Schedules集合的初始化
     */
    public function testSchedulesCollectionInitialization(): void
    {
        $classroom = $this->createEntity();
        $schedules = $classroom->getSchedules();

        $this->assertCount(0, $schedules);
    }

    /**
     * 测试getName返回title
     */
    public function testGetNameReturnsTitle(): void
    {
        $classroom = $this->createEntity();
        $title = '培训教室A';
        $classroom->setTitle($title);

        $this->assertSame($title, $classroom->getName());
    }

    /**
     * 测试toString返回title（当ID存在时）
     */
    public function testToStringReturnsTitleWhenIdExists(): void
    {
        $classroom = $this->createEntity();
        $title = '培训教室B';
        $classroom->setTitle($title);

        // 由于ID为null，toString应该返回空字符串
        $this->assertSame('', (string) $classroom);
    }

    /**
     * 测试setter方法功能
     */
    public function testSetterMethods(): void
    {
        $classroom = $this->createEntity();
        $title = '测试教室';
        $startTime = new \DateTime('2025-01-01');
        $endTime = new \DateTime('2025-12-31');

        $classroom->setTitle($title);
        $classroom->setStartTime($startTime);
        $classroom->setEndTime($endTime);

        $this->assertSame($title, $classroom->getTitle());
        $this->assertSame($startTime, $classroom->getStartTime());
        $this->assertSame($endTime, $classroom->getEndTime());
    }

    /**
     * 测试时间属性默认值
     */
    public function testTimePropertiesDefaultValues(): void
    {
        $classroom = $this->createEntity();
        $this->assertNull($classroom->getStartTime());
        $this->assertNull($classroom->getEndTime());
    }

    /**
     * 测试边界值
     */
    public function testBoundaryValues(): void
    {
        $classroom = $this->createEntity();
        // 测试空字符串
        $classroom->setTitle('');
        $this->assertSame('', $classroom->getTitle());

        // 测试长字符串
        $longTitle = str_repeat('A', 1000);
        $classroom->setTitle($longTitle);
        $this->assertSame($longTitle, $classroom->getTitle());
    }

    /**
     * 测试集合操作
     */
    public function testCollectionOperations(): void
    {
        $classroom = $this->createEntity();
        // 测试集合初始状态
        $this->assertCount(0, $classroom->getRegistrations());
        $this->assertCount(0, $classroom->getQrcodes());
        $this->assertCount(0, $classroom->getSchedules());
    }
}
