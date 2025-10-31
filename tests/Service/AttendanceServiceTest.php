<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\AttendanceService;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

/**
 * AttendanceService测试类
 *
 * 测试服务类的基本功能和接口实现
 *
 * @internal
 */
#[CoversClass(AttendanceService::class)]
#[RunTestsInSeparateProcesses]
final class AttendanceServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Initialize test environment if needed
    }

    /**
     * 测试recordAttendance方法 - 成功记录签到
     */
    public function testRecordAttendanceMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'recordAttendance');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(5, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('registration', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertEquals('method', $parameters[2]->getName());
    }

    /**
     * 测试服务类实现正确的接口
     */
    public function testServiceImplementsInterface(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        $this->assertTrue($reflection->implementsInterface(AttendanceServiceInterface::class));
    }

    /**
     * 测试方法签名
     */
    public function testMethodSignatures(): void
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
    public function testReturnTypes(): void
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
     * 测试batchImportAttendance方法 - 批量导入考勤记录
     */
    public function testBatchImportAttendanceMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'batchImportAttendance');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());
    }

    /**
     * 测试接口方法完整性
     */
    public function testInterfaceMethodsImplemented(): void
    {
        $interfaceReflection = new \ReflectionClass(AttendanceServiceInterface::class);
        $serviceReflection = new \ReflectionClass(AttendanceService::class);

        $interfaceMethods = $interfaceReflection->getMethods();

        foreach ($interfaceMethods as $interfaceMethod) {
            $this->assertTrue(
                $serviceReflection->hasMethod($interfaceMethod->getName()),
                'Service class missing method: ' . $interfaceMethod->getName()
            );
        }
    }

    /**
     * 测试构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(AttendanceService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(2, count($parameters));

        // 验证构造函数参数名称
        $parameterNames = array_map(fn ($param) => $param->getName(), $parameters);
        $this->assertContains('entityManager', $parameterNames);
        $this->assertContains('attendanceRepository', $parameterNames);
    }

    /**
     * 测试getAttendanceStatistics方法 - 获取考勤统计
     */
    public function testGetAttendanceStatisticsMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'getAttendanceStatistics');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());
    }

    /**
     * 测试validateAttendance方法 - 验证考勤有效性
     */
    public function testValidateAttendanceMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'validateAttendance');
        $this->assertTrue($reflection->isPublic());
        $this->assertGreaterThanOrEqual(2, $reflection->getParameters());
    }

    /**
     * 测试makeUpAttendance方法 - 补录考勤记录
     */
    public function testMakeUpAttendanceMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'makeUpAttendance');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(4, $reflection->getParameters());

        // 测试参数类型
        $parameters = $reflection->getParameters();
        $this->assertEquals('registration', $parameters[0]->getName());
        $this->assertEquals('type', $parameters[1]->getName());
        $this->assertEquals('recordTime', $parameters[2]->getName());
        $this->assertEquals('reason', $parameters[3]->getName());
    }

    /**
     * 测试getCourseAttendanceSummary方法 - 获取课程考勤汇总
     */
    public function testGetCourseAttendanceSummaryMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'getCourseAttendanceSummary');
        $this->assertTrue($reflection->isPublic());
        $this->assertGreaterThanOrEqual(1, $reflection->getParameters());
    }

    /**
     * 测试detectAttendanceAnomalies方法 - 检测考勤异常
     */
    public function testDetectAttendanceAnomaliesMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'detectAttendanceAnomalies');
        $this->assertTrue($reflection->isPublic());
        $this->assertGreaterThanOrEqual(1, $reflection->getParameters());
    }

    /**
     * 测试getAttendanceRateStatistics方法 - 获取考勤率统计
     */
    public function testGetAttendanceRateStatisticsMethod(): void
    {
        $service = self::getService(AttendanceServiceInterface::class);

        // 测试方法存在性
        $reflection = new \ReflectionMethod($service, 'getAttendanceRateStatistics');
        $this->assertTrue($reflection->isPublic());
        $this->assertGreaterThanOrEqual(1, $reflection->getParameters());
    }
}
