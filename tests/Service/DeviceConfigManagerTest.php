<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Service\DeviceConfigManager;

/**
 * @internal
 */
#[CoversClass(DeviceConfigManager::class)]
#[RunTestsInSeparateProcesses]
final class DeviceConfigManagerTest extends AbstractIntegrationTestCase
{
    private DeviceConfigManager $manager;

    protected function onSetUp(): void
    {
        $this->manager = self::getService(DeviceConfigManager::class);
    }

    public function testServiceExists(): void
    {
        $this->assertInstanceOf(DeviceConfigManager::class, $this->manager);
    }

    public function testValidateDeviceConfig(): void
    {
        // 测试有效的配置
        $validConfig = [
            'type' => 'face_recognition',
            'name' => '人脸识别设备1'
        ];

        $this->expectNotToPerformAssertions();
        $this->manager->validateDeviceConfig($validConfig);
    }

    public function testValidateDeviceConfigWithEmptyType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备类型不能为空');

        $invalidConfig = [
            'type' => '',
            'name' => '设备1'
        ];

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testValidateDeviceConfigWithMissingType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备类型不能为空');

        $invalidConfig = [
            'name' => '设备1'
        ];

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testValidateDeviceConfigWithEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备名称不能为空');

        $invalidConfig = [
            'type' => 'face_recognition',
            'name' => ''
        ];

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testAddDevice(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');

        $deviceConfig = [
            'type' => 'face_recognition',
            'name' => '人脸识别设备1'
        ];

        $getDevicesCallback = function ($classroom) {
            return $classroom->getDevices();
        };

        $result = $this->manager->addDevice($classroom, $deviceConfig, $getDevicesCallback);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('added_at', $result);
        $this->assertEquals($deviceConfig['type'], $result['type']);
        $this->assertEquals($deviceConfig['name'], $result['name']);
    }

    public function testRemoveDevice(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');

        $getDevicesCallback = function ($classroom) {
            return $classroom->getDevices();
        };

        // 首先添加一个设备
        $deviceConfig = [
            'id' => 'test_device_1',
            'type' => 'face_recognition',
            'name' => '人脸识别设备1'
        ];

        $devices = ['test_device_1' => $deviceConfig];
        $classroom->setDevices($devices);

        // 移除设备
        $result = $this->manager->removeDevice($classroom, 'test_device_1', $getDevicesCallback);

        $this->assertTrue($result);
        $this->assertEmpty($classroom->getDevices());
    }

    public function testRemoveNonExistentDevice(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');

        $getDevicesCallback = function ($classroom) {
            return $classroom->getDevices();
        };

        // 尝试移除不存在的设备
        $result = $this->manager->removeDevice($classroom, 'non_existent_device', $getDevicesCallback);

        $this->assertFalse($result);
    }

    public function testUpdateConfig(): void
    {
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');

        $getDevicesCallback = function ($classroom) {
            return $classroom->getDevices();
        };

        // 首先添加一个设备
        $initialConfig = [
            'id' => 'test_device_1',
            'type' => 'face_recognition',
            'name' => '人脸识别设备1'
        ];

        $devices = ['test_device_1' => $initialConfig];
        $classroom->setDevices($devices);

        // 更新设备配置
        $updateConfig = [
            'name' => '更新后的设备名称',
            'location' => '教室门口'
        ];

        $result = $this->manager->updateConfig($classroom, 'test_device_1', $updateConfig, $getDevicesCallback);

        $this->assertIsArray($result);
        $this->assertEquals('更新后的设备名称', $result['name']);
        $this->assertEquals('教室门口', $result['location']);
        $this->assertArrayHasKey('updated_at', $result);
        $this->assertEquals($initialConfig['type'], $result['type']); // 应该保持原有值
    }

    public function testUpdateConfigWithInvalidDevice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备不存在');

        $classroom = new Classroom();
        $classroom->setTitle('测试教室');

        $getDevicesCallback = function ($classroom) {
            return $classroom->getDevices();
        };

        $updateConfig = [
            'name' => '更新后的设备名称'
        ];

        $this->manager->updateConfig($classroom, 'non_existent_device', $updateConfig, $getDevicesCallback);
    }
}