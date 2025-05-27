<?php

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

/**
 * ClassroomScheduleRepository测试类
 * 
 * 测试仓储类的基本功能和方法存在性
 */
class ClassroomScheduleRepositoryTest extends TestCase
{
    /**
     * 测试仓储类的实例化
     */
    public function test_repository_class_exists(): void
    {
        $this->assertTrue(class_exists(ClassroomScheduleRepository::class));
    }

    /**
     * 测试仓储类继承正确的父类
     */
    public function test_repository_extends_service_entity_repository(): void
    {
        $reflection = new \ReflectionClass(ClassroomScheduleRepository::class);
        $this->assertTrue($reflection->isSubclassOf('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository'));
    }

    /**
     * 测试findByClassroom方法存在
     */
    public function test_findByClassroom_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findByClassroom'));
    }

    /**
     * 测试findByDateRange方法存在
     */
    public function test_findByDateRange_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findByDateRange'));
    }

    /**
     * 测试findByStatus方法存在
     */
    public function test_findByStatus_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findByStatus'));
    }

    /**
     * 测试findByType方法存在
     */
    public function test_findByType_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findByType'));
    }

    /**
     * 测试findConflictingSchedules方法存在
     */
    public function test_findConflictingSchedules_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findConflictingSchedules'));
    }

    /**
     * 测试findByTeacher方法存在
     */
    public function test_findByTeacher_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findByTeacher'));
    }

    /**
     * 测试countByClassroom方法存在
     */
    public function test_countByClassroom_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'countByClassroom'));
    }

    /**
     * 测试getClassroomUsageStats方法存在
     */
    public function test_getClassroomUsageStats_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'getClassroomUsageStats'));
    }

    /**
     * 测试findUpcomingSchedules方法存在
     */
    public function test_findUpcomingSchedules_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findUpcomingSchedules'));
    }

    /**
     * 测试findBySupplier方法存在
     */
    public function test_findBySupplier_method_exists(): void
    {
        $this->assertTrue(method_exists(ClassroomScheduleRepository::class, 'findBySupplier'));
    }

    /**
     * 测试方法参数类型
     */
    public function test_method_signatures(): void
    {
        $reflection = new \ReflectionClass(ClassroomScheduleRepository::class);
        
        // 测试findByClassroom方法签名
        $method = $reflection->getMethod('findByClassroom');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        
        // 测试findByDateRange方法签名
        $method = $reflection->getMethod('findByDateRange');
        $parameters = $method->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('startDate', $parameters[0]->getName());
        $this->assertEquals('endDate', $parameters[1]->getName());
    }

    /**
     * 测试返回类型
     */
    public function test_return_types(): void
    {
        $reflection = new \ReflectionClass(ClassroomScheduleRepository::class);
        
        // 测试countByClassroom返回类型
        $method = $reflection->getMethod('countByClassroom');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', $returnType->getName());
        
        // 测试findByClassroom返回类型
        $method = $reflection->getMethod('findByClassroom');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    /**
     * 测试冲突检测方法参数
     */
    public function test_conflict_detection_method(): void
    {
        $reflection = new \ReflectionClass(ClassroomScheduleRepository::class);
        
        // 测试findConflictingSchedules方法
        $method = $reflection->getMethod('findConflictingSchedules');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(3, count($parameters));
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startTime', $parameters[1]->getName());
        $this->assertEquals('endTime', $parameters[2]->getName());
    }
} 