<?php

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Repository\AttendanceRecordRepository;

/**
 * AttendanceRecordRepository测试类
 * 
 * 测试仓储类的基本功能和方法存在性
 */
class AttendanceRecordRepositoryTest extends TestCase
{
    /**
     * 测试仓储类的实例化
     */
    public function test_repository_class_exists(): void
    {
        $this->assertTrue(class_exists(AttendanceRecordRepository::class));
    }

    /**
     * 测试仓储类继承正确的父类
     */
    public function test_repository_extends_service_entity_repository(): void
    {
        $reflection = new \ReflectionClass(AttendanceRecordRepository::class);
        $this->assertTrue($reflection->isSubclassOf('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository'));
    }



    /**
     * 测试方法参数类型
     */
    public function test_method_signatures(): void
    {
        $reflection = new \ReflectionClass(AttendanceRecordRepository::class);
        
        // 测试findByRegistration方法签名
        $method = $reflection->getMethod('findByRegistration');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('registration', $parameters[0]->getName());
        
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
        $reflection = new \ReflectionClass(AttendanceRecordRepository::class);
        
        // 测试countByRegistration返回类型
        $method = $reflection->getMethod('countByRegistration');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', (string) $returnType);
        
        // 测试findByRegistration返回类型
        $method = $reflection->getMethod('findByRegistration');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }
} 