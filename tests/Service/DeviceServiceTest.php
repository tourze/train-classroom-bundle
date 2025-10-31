<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\DeviceService;
use Tourze\TrainClassroomBundle\Service\DeviceServiceInterface;

/**
 * DeviceService测试类
 *
 * 测试设备服务类的基本功能和接口实现
 *
 * @internal
 */
#[CoversClass(DeviceService::class)]
#[RunTestsInSeparateProcesses]
final class DeviceServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 无需额外设置
    }

    /**
     * 测试服务类实现正确的接口
     */
    public function testServiceImplementsInterface(): void
    {
        $service = self::getService(DeviceServiceInterface::class);
        $this->assertInstanceOf(DeviceService::class, $service);
        $this->assertInstanceOf(DeviceServiceInterface::class, $service);
    }

    /**
     * 测试getClassroomDevices方法
     */
    public function testGetClassroomDevicesMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'getClassroomDevices');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
    }

    /**
     * 测试addDevice方法
     */
    public function testAddDeviceMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'addDevice');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('deviceConfig', $parameters[1]->getName());
    }

    /**
     * 测试removeDevice方法
     */
    public function testRemoveDeviceMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'removeDevice');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('deviceId', $parameters[1]->getName());
    }

    /**
     * 测试performAttendanceVerification方法
     */
    public function testPerformAttendanceVerificationMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'performAttendanceVerification');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('method', $parameters[1]->getName());
        $this->assertEquals('data', $parameters[2]->getName());
    }

    /**
     * 测试服务类构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(DeviceService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);

        // 验证参数名称
        $this->assertEquals('entityManager', $parameters[0]->getName());
        $this->assertEquals('logger', $parameters[1]->getName());
    }

    /**
     * 测试updateDeviceConfig方法
     */
    public function testUpdateDeviceConfigMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'updateDeviceConfig');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('deviceId', $parameters[1]->getName());
        $this->assertEquals('config', $parameters[2]->getName());
    }

    /**
     * 测试checkDeviceStatus方法
     */
    public function testCheckDeviceStatusMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'checkDeviceStatus');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('deviceId', $parameters[1]->getName());
    }

    /**
     * 测试testDeviceConnection方法
     */
    public function testTestDeviceConnectionMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'testDeviceConnection');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('deviceConfig', $parameters[0]->getName());
    }

    /**
     * 测试getSupportedAttendanceMethods方法
     */
    public function testGetSupportedAttendanceMethodsMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'getSupportedAttendanceMethods');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
    }

    /**
     * 测试getRecordings方法
     */
    public function testGetRecordingsMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'getRecordings');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startTime', $parameters[1]->getName());
        $this->assertEquals('endTime', $parameters[2]->getName());
    }

    /**
     * 测试startRecording方法
     */
    public function testStartRecordingMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'startRecording');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('options', $parameters[1]->getName());
    }

    /**
     * 测试stopRecording方法
     */
    public function testStopRecordingMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'stopRecording');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
    }

    /**
     * 测试getEnvironmentData方法
     */
    public function testGetEnvironmentDataMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'getEnvironmentData');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('sensors', $parameters[1]->getName());
    }

    /**
     * 测试setEnvironmentThresholds方法
     */
    public function testSetEnvironmentThresholdsMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'setEnvironmentThresholds');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('thresholds', $parameters[1]->getName());
    }

    /**
     * 测试getDeviceLogs方法
     */
    public function testGetDeviceLogsMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'getDeviceLogs');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(4, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('deviceId', $parameters[1]->getName());
        $this->assertEquals('startTime', $parameters[2]->getName());
        $this->assertEquals('endTime', $parameters[3]->getName());
    }

    /**
     * 测试syncDeviceData方法
     */
    public function testSyncDeviceDataMethod(): void
    {
        $service = self::getService(DeviceServiceInterface::class);

        // 测试方法是否存在
        $reflection = new \ReflectionMethod($service, 'syncDeviceData');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        // 测试参数名称
        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('options', $parameters[1]->getName());
    }

    /**
     * 测试服务类可以被实例化
     */
    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getService(DeviceServiceInterface::class);
        $this->assertNotNull($service);
    }
}
