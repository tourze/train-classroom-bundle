<?php

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\AttendanceService;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

/**
 * AttendanceService测试类
 * 
 * 测试服务类的基本功能和接口实现
 */
class AttendanceServiceTest extends TestCase
{
    /**
     * 测试服务类存在
     */
    public function test_service_class_exists(): void
    {
        $this->assertTrue(class_exists(AttendanceService::class));
    }

    /**
     * 测试服务类实现正确的接口
     */
    public function test_service_implements_interface(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        $this->assertTrue($reflection->implementsInterface(AttendanceServiceInterface::class));
    }


    /**
     * 测试方法签名
     */
    public function test_method_signatures(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        
        // 测试recordAttendance方法签名
        $method = $reflection->getMethod('recordAttendance');
        $parameters = $method->getParameters();
        $this->assertGreaterThanOrEqual(3, count($parameters));
        $this->assertEquals('registration', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertEquals('method', $parameters[2]->getName());
        
        // 测试batchImportAttendance方法签名
        $method = $reflection->getMethod('batchImportAttendance');
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('attendanceData', $parameters[0]->getName());
    }

    /**
     * 测试返回类型
     */
    public function test_return_types(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        
        // 测试batchImportAttendance返回类型
        $method = $reflection->getMethod('batchImportAttendance');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
        
        // 测试getAttendanceStatistics返回类型
        $method = $reflection->getMethod('getAttendanceStatistics');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
        
        // 测试validateAttendance返回类型
        $method = $reflection->getMethod('validateAttendance');
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', (string) $returnType);
    }

    /**
     * 测试接口方法完整性
     */
    public function test_interface_methods_implemented(): void
    {
        $interfaceReflection = new \ReflectionClass(AttendanceServiceInterface::class);
        $serviceReflection = new \ReflectionClass(AttendanceService::class);
        
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
        $reflection = new \ReflectionClass(AttendanceService::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(2, count($parameters));
        
        // 验证构造函数参数名称
        $parameterNames = array_map(fn($param) => $param->getName(), $parameters);
        $this->assertContains('entityManager', $parameterNames);
        $this->assertContains('attendanceRepository', $parameterNames);
    }


    /**
     * 测试异常处理方法
     */
    public function test_exception_handling_methods(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        
        // 验证关键方法存在，这些方法应该包含异常处理逻辑
        $criticalMethods = [
            'recordAttendance',
            'batchImportAttendance',
            'makeUpAttendance',
            'validateAttendance'
        ];
        
        foreach ($criticalMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Critical method missing: $methodName"
            );
        }
    }
} 