<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;
use Tourze\TrainClassroomBundle\Service\DeviceConfigManager;
use Tourze\TrainClassroomBundle\Service\DeviceTester\DeviceTesterInterface;

/**
 * @internal
 */
#[CoversClass(DeviceConfigManager::class)]
final class DeviceConfigManagerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private LoggerInterface&MockObject $logger;
    private DeviceTesterInterface&MockObject $deviceTester;
    private DeviceConfigManager $manager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->deviceTester = $this->createMock(DeviceTesterInterface::class);

        $this->manager = new DeviceConfigManager(
            $this->entityManager,
            $this->logger,
            [$this->deviceTester]
        );
    }

    public function testAddDevice(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceConfig = [
            'type' => 'projector',
            'name' => 'Test Projector',
            'id' => 'device_001',
        ];

        $this->deviceTester
            ->expects(self::once())
            ->method('supports')
            ->with('projector')
            ->willReturn(true);

        $this->deviceTester
            ->expects(self::once())
            ->method('test')
            ->with(self::isArray())
            ->willReturn([
                'success' => true,
                'timestamp' => new \DateTime(),
            ]);

        $classroom
            ->expects(self::once())
            ->method('setDevices')
            ->with(self::isArray());

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->logger
            ->expects(self::once())
            ->method('info');

        $getDevicesCallback = static fn (Classroom $c): ?array => null;

        $result = $this->manager->addDevice($classroom, $deviceConfig, $getDevicesCallback);

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('type', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('added_at', $result);
        self::assertSame('projector', $result['type']);
        self::assertSame('Test Projector', $result['name']);
    }

    public function testRemoveDevice(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'device_001';

        $existingDevices = [
            'device_001' => [
                'type' => 'projector',
                'name' => 'Test Projector',
            ],
        ];

        $getDevicesCallback = static fn (Classroom $c): array => $existingDevices;

        $classroom
            ->expects(self::once())
            ->method('setDevices')
            ->with(self::callback(static function (array $devices): bool {
                return !isset($devices['device_001']);
            }));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('设备移除成功', self::isArray());

        $result = $this->manager->removeDevice($classroom, $deviceId, $getDevicesCallback);

        self::assertTrue($result);
    }

    public function testRemoveDeviceNotFound(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'non_existent_device';

        $existingDevices = [
            'device_001' => [
                'type' => 'projector',
                'name' => 'Test Projector',
            ],
        ];

        $getDevicesCallback = static fn (Classroom $c): array => $existingDevices;

        $classroom
            ->expects(self::never())
            ->method('setDevices');

        $this->entityManager
            ->expects(self::never())
            ->method('flush');

        $result = $this->manager->removeDevice($classroom, $deviceId, $getDevicesCallback);

        self::assertFalse($result);
    }

    public function testUpdateConfig(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'device_001';
        $newConfig = [
            'brightness' => 80,
        ];

        $existingDevices = [
            'device_001' => [
                'type' => 'projector',
                'name' => 'Test Projector',
                'brightness' => 50,
            ],
        ];

        $getDevicesCallback = static fn (Classroom $c): array => $existingDevices;

        $classroom
            ->expects(self::once())
            ->method('setDevices')
            ->with(self::callback(static function (array $devices) use ($deviceId): bool {
                return isset($devices[$deviceId])
                    && is_array($devices[$deviceId])
                    && 80 === $devices[$deviceId]['brightness']
                    && isset($devices[$deviceId]['updated_at']);
            }));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with('设备配置更新成功', self::isArray());

        $result = $this->manager->updateConfig($classroom, $deviceId, $newConfig, $getDevicesCallback);

        self::assertIsArray($result);
        self::assertArrayHasKey('brightness', $result);
        self::assertSame(80, $result['brightness']);
        self::assertArrayHasKey('type', $result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('updated_at', $result);
    }

    public function testUpdateConfigDeviceNotFound(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'non_existent_device';
        $newConfig = ['brightness' => 80];

        $existingDevices = [
            'device_001' => [
                'type' => 'projector',
                'name' => 'Test Projector',
            ],
        ];

        $getDevicesCallback = static fn (Classroom $c): array => $existingDevices;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备不存在');

        $this->manager->updateConfig($classroom, $deviceId, $newConfig, $getDevicesCallback);
    }

    public function testValidateDeviceConfig(): void
    {
        $validConfig = [
            'type' => 'projector',
            'name' => 'Test Projector',
        ];

        // This should not throw any exception
        $this->manager->validateDeviceConfig($validConfig);

        // If we reached here, validation passed
        $this->expectNotToPerformAssertions();
    }

    public function testValidateDeviceConfigMissingType(): void
    {
        $invalidConfig = [
            'name' => 'Test Projector',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备类型不能为空');

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testValidateDeviceConfigEmptyType(): void
    {
        $invalidConfig = [
            'type' => '',
            'name' => 'Test Projector',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备类型不能为空');

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testValidateDeviceConfigMissingName(): void
    {
        $invalidConfig = [
            'type' => 'projector',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备名称不能为空');

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testValidateDeviceConfigEmptyName(): void
    {
        $invalidConfig = [
            'type' => 'projector',
            'name' => '',
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备名称不能为空');

        $this->manager->validateDeviceConfig($invalidConfig);
    }

    public function testAddDeviceWithConnectionFailure(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceConfig = [
            'type' => 'projector',
            'name' => 'Test Projector',
        ];

        $this->deviceTester
            ->expects(self::once())
            ->method('supports')
            ->with('projector')
            ->willReturn(true);

        $this->deviceTester
            ->expects(self::once())
            ->method('test')
            ->willReturn([
                'success' => false,
                'error' => 'Connection timeout',
            ]);

        $getDevicesCallback = static fn (Classroom $c): ?array => null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('设备连接测试失败: Connection timeout');

        $this->manager->addDevice($classroom, $deviceConfig, $getDevicesCallback);
    }

    public function testRemoveDeviceWithNullDevices(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'device_001';

        $getDevicesCallback = static fn (Classroom $c): ?array => null;

        $result = $this->manager->removeDevice($classroom, $deviceId, $getDevicesCallback);

        self::assertFalse($result);
    }

    public function testUpdateConfigWithInvalidDevicesList(): void
    {
        $classroom = $this->createMock(Classroom::class);
        $deviceId = 'device_001';
        $newConfig = ['brightness' => 80];

        $getDevicesCallback = static fn (Classroom $c): ?array => null;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('设备列表无效');

        $this->manager->updateConfig($classroom, $deviceId, $newConfig, $getDevicesCallback);
    }
}
