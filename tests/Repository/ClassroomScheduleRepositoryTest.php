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
        $this->assertEquals('int', (string) $returnType);
        
        // 测试findByClassroom返回类型
        $method = $reflection->getMethod('findByClassroom');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
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