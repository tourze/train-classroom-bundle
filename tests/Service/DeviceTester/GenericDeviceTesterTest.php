<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service\DeviceTester;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\DeviceTester\DeviceTesterInterface;
use Tourze\TrainClassroomBundle\Service\DeviceTester\GenericDeviceTester;

/**
 * GenericDeviceTester测试类
 *
 * 测试通用设备测试器的功能
 *
 * @internal
 */
#[CoversClass(GenericDeviceTester::class)]
final class GenericDeviceTesterTest extends TestCase
{
    private GenericDeviceTester $tester;

    protected function setUp(): void
    {
        $this->tester = new GenericDeviceTester();
    }

    /**
     * 测试实现了接口
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(DeviceTesterInterface::class, $this->tester);
    }

    /**
     * 测试支持人脸识别设备
     */
    public function testSupportsFaceRecognition(): void
    {
        $this->assertTrue($this->tester->supports('face_recognition'));
    }

    /**
     * 测试支持指纹设备
     */
    public function testSupportsFingerprint(): void
    {
        $this->assertTrue($this->tester->supports('fingerprint'));
    }

    /**
     * 测试支持刷卡设备
     */
    public function testSupportsCardReader(): void
    {
        $this->assertTrue($this->tester->supports('card_reader'));
    }

    /**
     * 测试支持摄像头
     */
    public function testSupportsCamera(): void
    {
        $this->assertTrue($this->tester->supports('camera'));
    }

    /**
     * 测试支持环境传感器
     */
    public function testSupportsEnvironmentSensor(): void
    {
        $this->assertTrue($this->tester->supports('environment_sensor'));
    }

    /**
     * 测试不支持未知设备类型
     */
    public function testDoesNotSupportUnknownType(): void
    {
        $this->assertFalse($this->tester->supports('unknown_device'));
    }

    /**
     * 测试test方法
     */
    public function testTest(): void
    {
        $config = ['type' => 'face_recognition', 'ip' => '192.168.1.100'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertInstanceOf(\DateTime::class, $result['timestamp']);
    }

    /**
     * 测试人脸识别设备测试
     */
    public function testFaceRecognitionDevice(): void
    {
        $config = ['type' => 'face_recognition', 'ip' => '192.168.1.100'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('人脸识别设备连接正常', $result['message']);
        $this->assertInstanceOf(\DateTime::class, $result['timestamp']);
    }

    /**
     * 测试指纹设备测试
     */
    public function testFingerprintDevice(): void
    {
        $config = ['type' => 'fingerprint', 'port' => '/dev/ttyUSB0'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('指纹设备连接正常', $result['message']);
    }

    /**
     * 测试刷卡设备测试
     */
    public function testCardReaderDevice(): void
    {
        $config = ['type' => 'card_reader', 'port' => 'COM3'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('刷卡设备连接正常', $result['message']);
    }

    /**
     * 测试摄像头测试
     */
    public function testCameraDevice(): void
    {
        $config = ['type' => 'camera', 'url' => 'rtsp://example.com/stream'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('摄像头连接正常', $result['message']);
    }

    /**
     * 测试环境传感器测试
     */
    public function testEnvironmentSensorDevice(): void
    {
        $config = ['type' => 'environment_sensor', 'address' => '0x48'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('环境传感器连接正常', $result['message']);
    }

    /**
     * 测试未知设备类型
     */
    public function testUnknownDeviceType(): void
    {
        $config = ['type' => 'unknown'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('设备连接正常', $result['message']);
    }

    /**
     * 测试缺少type字段
     */
    public function testMissingTypeField(): void
    {
        $config = ['ip' => '192.168.1.100'];
        $result = $this->tester->test($config);

        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
        $this->assertEquals('设备连接正常', $result['message']);
    }
}
