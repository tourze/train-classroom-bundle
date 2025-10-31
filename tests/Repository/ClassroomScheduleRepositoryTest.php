<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(ClassroomScheduleRepository::class)]
#[RunTestsInSeparateProcesses]
final class ClassroomScheduleRepositoryTest extends AbstractRepositoryTestCase
{
    private ClassroomScheduleRepository $repository;

    private Classroom $classroom;

    private ClassroomSchedule $schedule;

    private Catalog $category;

    private Course $course;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ClassroomScheduleRepository::class);

        // 为所有测试创建基础数据
        $this->createTestData();
    }

    private function createTestData(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test-' . uniqid());
        $catalogType->setName('测试类型');
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $this->category = new Catalog();
        $this->category->setType($catalogType);
        $this->category->setName('测试分类');
        $this->category->setEnabled(true);
        self::getEntityManager()->persist($this->category);

        $this->course = new Course();
        $this->course->setTitle('测试课程');
        $this->course->setDescription('测试课程描述');
        $this->course->setLearnHour(10);
        $this->course->setCategory($this->category);
        self::getEntityManager()->persist($this->course);

        $this->classroom = new Classroom();
        $this->classroom->setTitle('测试教室');
        $this->classroom->setCategory($this->category);
        $this->classroom->setCourse($this->course);
        $this->classroom->setType('PHYSICAL');
        $this->classroom->setStatus('ACTIVE');
        self::getEntityManager()->persist($this->classroom);

        $this->schedule = new ClassroomSchedule();
        $this->schedule->setClassroom($this->classroom);
        $this->schedule->setTeacherId('TEACHER_001');
        $this->schedule->setScheduleDate(new \DateTimeImmutable('2024-01-15'));
        $this->schedule->setStartTime(new \DateTimeImmutable('2024-01-15 09:00:00'));
        $this->schedule->setEndTime(new \DateTimeImmutable('2024-01-15 10:30:00'));
        $this->schedule->setScheduleType(ScheduleType::REGULAR);
        $this->schedule->setScheduleStatus(ScheduleStatus::SCHEDULED);
        $this->schedule->setCourseContent('Java基础编程');
        $this->schedule->setExpectedStudents(30);
        $this->repository->save($this->schedule);

        self::getEntityManager()->flush();
    }

    public function testFindByClassroom(): void
    {
        $schedules = $this->repository->findByClassroom($this->classroom);

        $this->assertIsArray($schedules);
        $this->assertCount(1, $schedules);
        $this->assertInstanceOf(ClassroomSchedule::class, $schedules[0]);
        $this->assertEquals($this->classroom->getId(), $schedules[0]->getClassroom()->getId());
    }

    public function testFindByDate(): void
    {
        // 确保测试数据存在
        $this->assertNotNull($this->schedule, 'Schedule should be created in setUp');
        $this->assertNotNull($this->schedule->getId(), 'Schedule should have been persisted with an ID');

        // 使用实际保存的日期进行查询
        $actualDate = $this->schedule->getScheduleDate();
        $this->assertNotNull($actualDate, 'Schedule should have a scheduleDate');

        $schedules = $this->repository->findByDate($actualDate);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules), sprintf('Should find at least one schedule for date %s', $actualDate->format('Y-m-d')));
        $this->assertEquals($actualDate->format('Y-m-d'), $schedules[0]->getScheduleDate()->format('Y-m-d'));
    }

    public function testFindByDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $schedules = $this->repository->findByDateRange($startDate, $endDate);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertGreaterThanOrEqual($startDate, $schedule->getScheduleDate());
            $this->assertLessThanOrEqual($endDate, $schedule->getScheduleDate());
        }
    }

    public function testFindByStatus(): void
    {
        $schedules = $this->repository->findByStatus(ScheduleStatus::SCHEDULED);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals(ScheduleStatus::SCHEDULED, $schedule->getScheduleStatus());
        }
    }

    public function testFindByType(): void
    {
        $schedules = $this->repository->findByType(ScheduleType::REGULAR);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals(ScheduleType::REGULAR, $schedule->getScheduleType());
        }
    }

    public function testFindByTeacher(): void
    {
        $schedules = $this->repository->findByTeacher('TEACHER_001');

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals('TEACHER_001', $schedule->getTeacherId());
        }
    }

    public function testFindConflictingSchedules(): void
    {
        $startTime = new \DateTimeImmutable('2024-01-15 09:30:00');
        $endTime = new \DateTimeImmutable('2024-01-15 11:00:00');

        $conflicts = $this->repository->findConflictingSchedules(
            $this->classroom,
            $startTime,
            $endTime
        );

        $this->assertIsArray($conflicts);
        $this->assertGreaterThanOrEqual(1, count($conflicts));
    }

    public function testFindConflictingSchedulesWithExclude(): void
    {
        $startTime = new \DateTimeImmutable('2024-01-15 09:30:00');
        $endTime = new \DateTimeImmutable('2024-01-15 11:00:00');

        $conflicts = $this->repository->findConflictingSchedules(
            $this->classroom,
            $startTime,
            $endTime,
            $this->schedule->getId()
        );

        $this->assertIsArray($conflicts);
        $this->assertCount(0, $conflicts);
    }

    public function testFindTeacherConflictingSchedules(): void
    {
        $startTime = new \DateTimeImmutable('2024-01-15 09:30:00');
        $endTime = new \DateTimeImmutable('2024-01-15 11:00:00');

        $conflicts = $this->repository->findTeacherConflictingSchedules(
            'TEACHER_001',
            $startTime,
            $endTime
        );

        $this->assertIsArray($conflicts);
        $this->assertGreaterThanOrEqual(1, count($conflicts));
    }

    public function testFindActiveSchedules(): void
    {
        $schedules = $this->repository->findActiveSchedules();

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertContains(
                $schedule->getScheduleStatus(),
                [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING]
            );
        }
    }

    public function testFindOngoingSchedules(): void
    {
        $ongoingSchedule = new ClassroomSchedule();
        $ongoingSchedule->setClassroom($this->classroom);
        $ongoingSchedule->setTeacherId('TEACHER_002');
        $now = new \DateTimeImmutable();
        $ongoingSchedule->setScheduleDate($now);
        $ongoingSchedule->setStartTime($now->modify('-30 minutes'));
        $ongoingSchedule->setEndTime($now->modify('+30 minutes'));
        $ongoingSchedule->setScheduleType(ScheduleType::REGULAR);
        $ongoingSchedule->setScheduleStatus(ScheduleStatus::ONGOING);
        $this->repository->save($ongoingSchedule);

        $schedules = $this->repository->findOngoingSchedules();

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals(ScheduleStatus::ONGOING, $schedule->getScheduleStatus());
        }
    }

    public function testFindUpcomingSchedules(): void
    {
        $upcomingSchedule = new ClassroomSchedule();
        $upcomingSchedule->setClassroom($this->classroom);
        $upcomingSchedule->setTeacherId('TEACHER_003');
        $now = new \DateTimeImmutable();
        $upcomingSchedule->setScheduleDate($now);
        $upcomingSchedule->setStartTime($now->modify('+15 minutes'));
        $upcomingSchedule->setEndTime($now->modify('+75 minutes'));
        $upcomingSchedule->setScheduleType(ScheduleType::REGULAR);
        $upcomingSchedule->setScheduleStatus(ScheduleStatus::SCHEDULED);
        $this->repository->save($upcomingSchedule);

        $schedules = $this->repository->findUpcomingSchedules(30);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals(ScheduleStatus::SCHEDULED, $schedule->getScheduleStatus());
        }
    }

    public function testCountByType(): void
    {
        $count = $this->repository->countByType(ScheduleType::REGULAR);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByClassroom(): void
    {
        $count = $this->repository->countByClassroom($this->classroom);

        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testGetClassroomUsageStats(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $stats = $this->repository->getClassroomUsageStats($startDate, $endDate);

        $this->assertIsArray($stats);
        if (count($stats) > 0) {
            $this->assertArrayHasKey('classroom_id', $stats[0]);
            $this->assertArrayHasKey('classroom_name', $stats[0]);
            $this->assertArrayHasKey('schedule_count', $stats[0]);
        }
    }

    public function testFindBySupplier(): void
    {
        // 注意：当前 ClassroomSchedule 实体不支持 supplierId 字段
        // 此测试验证方法返回空数组的行为
        $schedules = $this->repository->findBySupplier('SUPPLIER_123');

        $this->assertIsArray($schedules);
        $this->assertCount(0, $schedules);
    }

    public function testFindSchedulesToUpdateStatus(): void
    {
        $schedules = $this->repository->findSchedulesToUpdateStatus();

        $this->assertIsArray($schedules);
    }

    public function testFindActiveSchedulesByClassroom(): void
    {
        $schedules = $this->repository->findActiveSchedulesByClassroom($this->classroom);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals($this->classroom->getId(), $schedule->getClassroom()->getId());
            $this->assertContains(
                $schedule->getScheduleStatus(),
                [ScheduleStatus::SCHEDULED, ScheduleStatus::ONGOING]
            );
        }
    }

    public function testGetClassroomUtilizationRate(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $utilization = $this->repository->getClassroomUtilizationRate(
            $this->classroom,
            $startDate,
            $endDate
        );

        $this->assertIsArray($utilization);
        $this->assertArrayHasKey('total_hours', $utilization);
        $this->assertArrayHasKey('available_hours', $utilization);
        $this->assertArrayHasKey('utilization_rate', $utilization);
        $this->assertIsFloat($utilization['total_hours']);
        $this->assertIsInt($utilization['available_hours']);
        $this->assertIsFloat($utilization['utilization_rate']);
    }

    public function testFindSchedulesInDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $schedules = $this->repository->findSchedulesInDateRange($startDate, $endDate);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
    }

    public function testFindSchedulesInDateRangeWithClassroomFilter(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $classroomId = $this->classroom->getId();
        $this->assertNotNull($classroomId);
        $classroomIds = [$classroomId];

        $schedules = $this->repository->findSchedulesInDateRange($startDate, $endDate, $classroomIds);

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertContains($schedule->getClassroom()->getId(), $classroomIds);
        }
    }

    public function testFindSchedulesByClassroomAndDateRange(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $schedules = $this->repository->findSchedulesByClassroomAndDateRange(
            $this->classroom,
            $startDate,
            $endDate
        );

        $this->assertIsArray($schedules);
        $this->assertGreaterThanOrEqual(1, count($schedules));
        foreach ($schedules as $schedule) {
            $this->assertEquals($this->classroom->getId(), $schedule->getClassroom()->getId());
        }
    }

    public function testGetScheduleStatisticsReport(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');

        $report = $this->repository->getScheduleStatisticsReport($startDate, $endDate);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('total_schedules', $report);
        $this->assertArrayHasKey('total_classrooms', $report);
        $this->assertArrayHasKey('total_teachers', $report);
        $this->assertArrayHasKey('completed_schedules', $report);
        $this->assertArrayHasKey('cancelled_schedules', $report);
        $this->assertArrayHasKey('total_expected_students', $report);
        $this->assertArrayHasKey('total_actual_students', $report);
    }

    public function testGetScheduleStatisticsReportWithFilters(): void
    {
        $startDate = new \DateTimeImmutable('2024-01-01');
        $endDate = new \DateTimeImmutable('2024-01-31');
        $filters = [
            'classroom_id' => $this->classroom->getId(),
            'teacher_id' => 'TEACHER_001',
        ];

        $report = $this->repository->getScheduleStatisticsReport($startDate, $endDate, $filters);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('total_schedules', $report);
    }

    public function testSaveAndRemove(): void
    {
        $newSchedule = new ClassroomSchedule();
        $newSchedule->setClassroom($this->classroom);
        $newSchedule->setTeacherId('TEACHER_NEW');
        $newSchedule->setScheduleDate(new \DateTimeImmutable('2024-01-20'));
        $newSchedule->setStartTime(new \DateTimeImmutable('2024-01-20 14:00:00'));
        $newSchedule->setEndTime(new \DateTimeImmutable('2024-01-20 15:30:00'));
        $newSchedule->setScheduleType(ScheduleType::MAKEUP);
        $newSchedule->setScheduleStatus(ScheduleStatus::SCHEDULED);

        $this->repository->save($newSchedule);
        $scheduleId = $newSchedule->getId();
        $this->assertNotNull($scheduleId);

        $this->repository->remove($newSchedule);

        // 清理 EntityManager 缓存以确保从数据库查询
        self::getEntityManager()->clear();

        $found = $this->repository->find($scheduleId);
        $this->assertNull($found);
    }

    public function testRemove(): void
    {
        $newSchedule = new ClassroomSchedule();
        $newSchedule->setClassroom($this->classroom);
        $newSchedule->setTeacherId('TEACHER_REMOVE');
        $newSchedule->setScheduleDate(new \DateTimeImmutable('2024-01-21'));
        $newSchedule->setStartTime(new \DateTimeImmutable('2024-01-21 10:00:00'));
        $newSchedule->setEndTime(new \DateTimeImmutable('2024-01-21 11:30:00'));
        $newSchedule->setScheduleType(ScheduleType::REGULAR);
        $newSchedule->setScheduleStatus(ScheduleStatus::SCHEDULED);

        $this->repository->save($newSchedule);
        $scheduleId = $newSchedule->getId();
        $this->assertNotNull($scheduleId);

        $this->repository->remove($newSchedule);
        $found = $this->repository->find($scheduleId);
        $this->assertNull($found);
    }

    protected function createNewEntity(): object
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test-' . uniqid());
        $catalogType->setName('测试类型');
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setType($catalogType);
        $category->setName('测试分类 ' . uniqid());
        $category->setEnabled(true);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('测试课程 ' . uniqid());
        $course->setDescription('测试课程描述');
        $course->setLearnHour(10);
        $course->setCategory($category);
        self::getEntityManager()->persist($course);

        $classroom = new Classroom();
        $classroom->setTitle('测试教室 ' . uniqid());
        $classroom->setCategory($category);
        $classroom->setCourse($course);
        $classroom->setType('PHYSICAL');
        $classroom->setStatus('ACTIVE');
        self::getEntityManager()->persist($classroom);

        $schedule = new ClassroomSchedule();
        $schedule->setClassroom($classroom);
        $schedule->setTeacherId('TEACHER_' . uniqid());
        $schedule->setScheduleDate(new \DateTimeImmutable());
        $schedule->setStartTime(new \DateTimeImmutable('09:00:00'));
        $schedule->setEndTime(new \DateTimeImmutable('10:30:00'));
        $schedule->setScheduleType(ScheduleType::REGULAR);
        $schedule->setScheduleStatus(ScheduleStatus::SCHEDULED);

        return $schedule;
    }

    /**
     * @return ServiceEntityRepository<ClassroomSchedule>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
