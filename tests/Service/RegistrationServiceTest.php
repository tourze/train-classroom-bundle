<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\RegistrationService;

/**
 * RegistrationService测试类
 *
 * 测试报名服务类的基本功能
 *
 * @internal
 */
#[CoversClass(RegistrationService::class)]
#[RunTestsInSeparateProcesses]
final class RegistrationServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Initialize test environment if needed
    }

    /**
     * 测试服务类可以被实例化
     */
    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getService(RegistrationService::class);
        $this->assertNotNull($service);
        $this->assertInstanceOf(RegistrationService::class, $service);
    }

    /**
     * 测试findByStatus方法存在并能正确调用
     */
    public function testFindByStatusMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findByStatus');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('status', $parameters[0]->getName());
        $this->assertEquals('limit', $parameters[1]->getName());
        $this->assertEquals('offset', $parameters[2]->getName());
    }

    /**
     * 测试findByCourse方法存在并能正确调用
     */
    public function testFindByCourseMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findByCourse');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('course', $parameters[0]->getName());
        $this->assertEquals('limit', $parameters[1]->getName());
        $this->assertEquals('offset', $parameters[2]->getName());
    }

    /**
     * 测试findByTrainType方法存在并能正确调用
     */
    public function testFindByTrainTypeMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findByTrainType');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('trainType', $parameters[0]->getName());
        $this->assertEquals('limit', $parameters[1]->getName());
        $this->assertEquals('offset', $parameters[2]->getName());
    }

    /**
     * 测试findFinishedRegistrations方法存在并能正确调用
     */
    public function testFindFinishedRegistrationsMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findFinishedRegistrations');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('limit', $parameters[0]->getName());
        $this->assertEquals('offset', $parameters[1]->getName());
    }

    /**
     * 测试findUnfinishedRegistrations方法存在并能正确调用
     */
    public function testFindUnfinishedRegistrationsMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findUnfinishedRegistrations');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('limit', $parameters[0]->getName());
        $this->assertEquals('offset', $parameters[1]->getName());
    }

    /**
     * 测试findLatestUserRegistrationByStatus方法存在并能正确调用
     */
    public function testFindLatestUserRegistrationByStatusMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findLatestUserRegistrationByStatus');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('student', $parameters[0]->getName());
        $this->assertEquals('status', $parameters[1]->getName());
    }

    /**
     * 测试findById方法
     */
    public function testFindByIdMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findById');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('id', $parameters[0]->getName());
    }

    /**
     * 测试findUserRegistrations方法
     */
    public function testFindUserRegistrationsMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findUserRegistrations');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('student', $parameters[0]->getName());
    }

    /**
     * 测试save方法
     */
    public function testSaveMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'save');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('registration', $parameters[0]->getName());
        $this->assertEquals('flush', $parameters[1]->getName());
    }

    /**
     * 测试findUserCourseRegistration方法
     */
    public function testFindUserCourseRegistrationMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'findUserCourseRegistration');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('student', $parameters[0]->getName());
        $this->assertEquals('courseId', $parameters[1]->getName());
    }

    /**
     * 测试isUserRegisteredForCourse方法
     */
    public function testIsUserRegisteredForCourseMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'isUserRegisteredForCourse');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('student', $parameters[0]->getName());
        $this->assertEquals('courseId', $parameters[1]->getName());
    }

    /**
     * 测试getActiveUserRegistrations方法
     */
    public function testGetActiveUserRegistrationsMethod(): void
    {
        $service = self::getService(RegistrationService::class);

        $reflection = new \ReflectionMethod($service, 'getActiveUserRegistrations');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(1, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('student', $parameters[0]->getName());
    }

    /**
     * 测试服务类构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(RegistrationService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(3, $parameters);

        $this->assertEquals('registrationRepository', $parameters[0]->getName());
        $this->assertEquals('entityManager', $parameters[1]->getName());
        $this->assertEquals('logger', $parameters[2]->getName());
    }
}
