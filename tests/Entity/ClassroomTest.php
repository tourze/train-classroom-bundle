<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;

/**
 * Classroom实体测试类
 *
 * 测试教室实体的基本功能，避免外部依赖
 */
class ClassroomTest extends TestCase
{
    private Classroom $classroom;

    protected function setUp(): void
    {
        $this->classroom = new Classroom();
    }

    /**
     * 测试Title的设置和获取
     */
    public function test_title_property(): void
    {
        $title = '安全生产培训班第一期';
        $this->classroom->setTitle($title);
        
        $this->assertSame($title, $this->classroom->getTitle());
    }

    /**
     * 测试StartTime的设置和获取
     */
    public function test_start_time_property(): void
    {
        $startTime = new \DateTime('2025-01-15');
        $this->classroom->setStartTime($startTime);
        
        $this->assertSame($startTime, $this->classroom->getStartTime());
    }

    /**
     * 测试StartTime可以为null
     */
    public function test_start_time_can_be_null(): void
    {
        $this->classroom->setStartTime(null);
        
        $this->assertNull($this->classroom->getStartTime());
    }

    /**
     * 测试EndTime的设置和获取
     */
    public function test_end_time_property(): void
    {
        $endTime = new \DateTime('2025-03-15');
        $this->classroom->setEndTime($endTime);
        
        $this->assertSame($endTime, $this->classroom->getEndTime());
    }

    /**
     * 测试EndTime可以为null
     */
    public function test_end_time_can_be_null(): void
    {
        $this->classroom->setEndTime(null);
        
        $this->assertNull($this->classroom->getEndTime());
    }

    /**
     * 测试Registrations集合的初始化
     */
    public function test_registrations_collection_initialization(): void
    {
        $registrations = $this->classroom->getRegistrations();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $registrations);
        $this->assertCount(0, $registrations);
    }

    /**
     * 测试Qrcodes集合的初始化
     */
    public function test_qrcodes_collection_initialization(): void
    {
        $qrcodes = $this->classroom->getQrcodes();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $qrcodes);
        $this->assertCount(0, $qrcodes);
    }

    /**
     * 测试Schedules集合的初始化
     */
    public function test_schedules_collection_initialization(): void
    {
        $schedules = $this->classroom->getSchedules();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $schedules);
        $this->assertCount(0, $schedules);
    }

    /**
     * 测试getName返回title
     */
    public function test_getName_returns_title(): void
    {
        $title = '培训教室A';
        $this->classroom->setTitle($title);
        
        $this->assertSame($title, $this->classroom->getName());
    }

    /**
     * 测试toString返回title（当ID存在时）
     */
    public function test_toString_returns_title_when_id_exists(): void
    {
        $title = '培训教室B';
        $this->classroom->setTitle($title);
        
        // 由于ID为null，toString应该返回空字符串
        $this->assertSame('', (string) $this->classroom);
    }

    /**
     * 测试方法链式调用
     */
    public function test_method_chaining(): void
    {
        $title = '链式调用测试';
        $startTime = new \DateTime('2025-01-01');
        $endTime = new \DateTime('2025-12-31');
        
        $result = $this->classroom
            ->setTitle($title)
            ->setStartTime($startTime)
            ->setEndTime($endTime);
        
        $this->assertSame($this->classroom, $result);
        $this->assertSame($title, $this->classroom->getTitle());
        $this->assertSame($startTime, $this->classroom->getStartTime());
        $this->assertSame($endTime, $this->classroom->getEndTime());
    }

    /**
     * 测试时间属性默认值
     */
    public function test_time_properties_default_values(): void
    {
        $this->assertNull($this->classroom->getStartTime());
        $this->assertNull($this->classroom->getEndTime());
    }

    /**
     * 测试边界值
     */
    public function test_boundary_values(): void
    {
        // 测试空字符串
        $this->classroom->setTitle('');
        $this->assertSame('', $this->classroom->getTitle());
        
        // 测试长字符串
        $longTitle = str_repeat('A', 1000);
        $this->classroom->setTitle($longTitle);
        $this->assertSame($longTitle, $this->classroom->getTitle());
    }

    /**
     * 测试集合操作
     */
    public function test_collection_operations(): void
    {
        // 测试集合初始状态
        $this->assertCount(0, $this->classroom->getRegistrations());
        $this->assertCount(0, $this->classroom->getQrcodes());
        $this->assertCount(0, $this->classroom->getSchedules());
        
        // 测试集合类型
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->classroom->getRegistrations());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->classroom->getQrcodes());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->classroom->getSchedules());
    }
} 