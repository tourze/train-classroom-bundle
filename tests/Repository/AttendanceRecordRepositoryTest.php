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
     * 测试findByRegistration方法存在
     */
    public function test_findByRegistration_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findByRegistration'));
    }

    /**
     * 测试findByDateRange方法存在
     */
    public function test_findByDateRange_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findByDateRange'));
    }

    /**
     * 测试findByType方法存在
     */
    public function test_findByType_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findByType'));
    }

    /**
     * 测试findSuccessfulRecords方法存在
     */
    public function test_findSuccessfulRecords_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findSuccessfulRecords'));
    }

    /**
     * 测试countByRegistration方法存在
     */
    public function test_countByRegistration_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'countByRegistration'));
    }

    /**
     * 测试countSuccessfulByRegistration方法存在
     */
    public function test_countSuccessfulByRegistration_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'countSuccessfulByRegistration'));
    }

    /**
     * 测试findLatestByRegistration方法存在
     */
    public function test_findLatestByRegistration_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findLatestByRegistration'));
    }

    /**
     * 测试findByDevice方法存在
     */
    public function test_findByDevice_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findByDevice'));
    }

    /**
     * 测试getDailyAttendanceStats方法存在
     */
    public function test_getDailyAttendanceStats_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'getDailyAttendanceStats'));
    }

    /**
     * 测试findAnomalousRecords方法存在
     */
    public function test_findAnomalousRecords_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findAnomalousRecords'));
    }

    /**
     * 测试findBySupplier方法存在
     */
    public function test_findBySupplier_method_exists(): void
    {
        $this->assertTrue(method_exists(AttendanceRecordRepository::class, 'findBySupplier'));
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
        $this->assertEquals('int', $returnType->getName());
        
        // 测试findByRegistration返回类型
        $method = $reflection->getMethod('findByRegistration');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }
} 