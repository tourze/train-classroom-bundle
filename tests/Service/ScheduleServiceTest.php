<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\ScheduleService;
use Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface;

/**
 * ScheduleService测试类
 *
 * 测试排课服务类的基本功能和接口实现
 *
 * @internal
 */
#[CoversClass(ScheduleService::class)]
#[RunTestsInSeparateProcesses]
final class ScheduleServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Initialize test environment if needed
    }

    /**
     * 测试服务类实现正确的接口
     */
    public function testServiceImplementsInterface(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);
        $this->assertInstanceOf(ScheduleService::class, $service);
        $this->assertInstanceOf(ScheduleServiceInterface::class, $service);
    }

    /**
     * 测试createSchedule方法
     */
    public function testCreateScheduleMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'createSchedule');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(6, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('courseId', $parameters[1]->getName());
        $this->assertEquals('type', $parameters[2]->getName());
        $this->assertEquals('startTime', $parameters[3]->getName());
        $this->assertEquals('endTime', $parameters[4]->getName());
        $this->assertEquals('options', $parameters[5]->getName());
    }

    /**
     * 测试updateScheduleStatus方法
     */
    public function testUpdateScheduleStatusMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'updateScheduleStatus');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('schedule', $parameters[0]->getName());
        $this->assertEquals('status', $parameters[1]->getName());
        $this->assertEquals('reason', $parameters[2]->getName());
    }

    /**
     * 测试cancelSchedule方法
     */
    public function testCancelScheduleMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'cancelSchedule');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('schedule', $parameters[0]->getName());
        $this->assertEquals('reason', $parameters[1]->getName());
    }

    /**
     * 测试detectScheduleConflicts方法
     */
    public function testDetectScheduleConflictsMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'detectScheduleConflicts');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(4, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startTime', $parameters[1]->getName());
        $this->assertEquals('endTime', $parameters[2]->getName());
        $this->assertEquals('excludeScheduleId', $parameters[3]->getName());
    }

    /**
     * 测试服务类构造函数参数
     */
    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(ScheduleService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(4, $parameters);

        $this->assertEquals('entityManager', $parameters[0]->getName());
        $this->assertEquals('scheduleRepository', $parameters[1]->getName());
        $this->assertEquals('classroomRepository', $parameters[2]->getName());
        $this->assertEquals('logger', $parameters[3]->getName());
    }

    /**
     * 测试getClassroomUtilizationRate方法
     */
    public function testGetClassroomUtilizationRateMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'getClassroomUtilizationRate');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startDate', $parameters[1]->getName());
        $this->assertEquals('endDate', $parameters[2]->getName());
    }

    /**
     * 测试findAvailableClassrooms方法
     */
    public function testFindAvailableClassroomsMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'findAvailableClassrooms');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(4, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('startTime', $parameters[0]->getName());
        $this->assertEquals('endTime', $parameters[1]->getName());
        $this->assertEquals('minCapacity', $parameters[2]->getName());
        $this->assertEquals('requiredFeatures', $parameters[3]->getName());
    }

    /**
     * 测试batchCreateSchedules方法
     */
    public function testBatchCreateSchedulesMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'batchCreateSchedules');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(2, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('scheduleData', $parameters[0]->getName());
        $this->assertEquals('skipConflicts', $parameters[1]->getName());
    }

    /**
     * 测试postponeSchedule方法
     */
    public function testPostponeScheduleMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'postponeSchedule');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(4, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('schedule', $parameters[0]->getName());
        $this->assertEquals('newStartTime', $parameters[1]->getName());
        $this->assertEquals('newEndTime', $parameters[2]->getName());
        $this->assertEquals('reason', $parameters[3]->getName());
    }

    /**
     * 测试getScheduleCalendar方法
     */
    public function testGetScheduleCalendarMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'getScheduleCalendar');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('startDate', $parameters[0]->getName());
        $this->assertEquals('endDate', $parameters[1]->getName());
        $this->assertEquals('classroomIds', $parameters[2]->getName());
    }

    /**
     * 测试getScheduleStatisticsReport方法
     */
    public function testGetScheduleStatisticsReportMethod(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);

        $reflection = new \ReflectionMethod($service, 'getScheduleStatisticsReport');
        $this->assertTrue($reflection->isPublic());
        $this->assertCount(3, $reflection->getParameters());

        $parameters = $reflection->getParameters();
        $this->assertEquals('startDate', $parameters[0]->getName());
        $this->assertEquals('endDate', $parameters[1]->getName());
        $this->assertEquals('filters', $parameters[2]->getName());
    }

    /**
     * 测试服务类可以被实例化
     */
    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getService(ScheduleServiceInterface::class);
        $this->assertNotNull($service);
    }
}
