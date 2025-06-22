<?php

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\ScheduleService;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

/**
 * ScheduleService测试类
 * 
 * 测试排课服务类的基本功能和接口实现
 */
class ScheduleServiceTest extends TestCase
{
    /**
     * 测试服务类存在
     */
    public function test_service_class_exists(): void
    {
        $this->assertTrue(class_exists(ScheduleService::class));
    }

    /**
     * 测试服务类实现正确的接口
     */
    public function test_service_implements_interface(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        $this->assertTrue($reflection->implementsInterface(ScheduleServiceInterface::class));
    }


    /**
     * 测试方法签名
     */
    public function test_method_signatures(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        
        // 测试createSchedule方法签名
        $method = $reflection->getMethod('createSchedule');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(5, count($parameters));
        
        // 测试detectScheduleConflicts方法签名
        $method = $reflection->getMethod('detectScheduleConflicts');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(3, count($parameters));
    }

    /**
     * 测试返回类型
     */
    public function test_return_types(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        
        // 测试batchCreateSchedules返回类型
        $method = $reflection->getMethod('batchCreateSchedules');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
        
        // 测试getClassroomUtilizationRate返回类型
        $method = $reflection->getMethod('getClassroomUtilizationRate');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
        
        // 测试detectScheduleConflicts返回类型
        $method = $reflection->getMethod('detectScheduleConflicts');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    /**
     * 测试接口方法完整性
     */
    public function test_interface_methods_implemented(): void
    {
        $interfaceReflection = new \ReflectionClass(ScheduleServiceInterface::class);
        $serviceReflection = new \ReflectionClass(ScheduleService::class);
        
        $interfaceMethods = $interfaceReflection->getMethods();
        
        foreach ($interfaceMethods as $interfaceMethod) {
            $this->assertTrue(
                $serviceReflection->hasMethod($interfaceMethod->getName()),
                "Service class missing method: " . $interfaceMethod->getName()
            );
        }
    }

    /**
     * 测试构造函数参数
     */
    public function test_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(2, count($parameters));
        
        // 验证构造函数参数名称
        $parameterNames = array_map(fn($param) => $param->getName(), $parameters);
        $this->assertContains('entityManager', $parameterNames);
        $this->assertContains('scheduleRepository', $parameterNames);
    }


    /**
     * 测试状态管理方法
     */
    public function test_status_management_methods(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        
        // 验证状态管理方法存在
        $statusMethods = [
            'updateScheduleStatus',
            'cancelSchedule'
        ];
        
        foreach ($statusMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Status management method missing: $methodName"
            );
        }
    }

    /**
     * 测试统计分析方法
     */
    public function test_statistics_methods(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        
        // 验证统计方法存在
        $statsMethods = [
            'getClassroomUtilizationRate',
            'getScheduleStatisticsReport'
        ];
        
        foreach ($statsMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Statistics method missing: $methodName"
            );
        }
    }

} 