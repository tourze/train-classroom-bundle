<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;
use Tourze\TrainClassroomBundle\Repository\AttendanceRecordRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(AttendanceRecordRepository::class)]
#[RunTestsInSeparateProcesses]
final class AttendanceRecordRepositoryTest extends AbstractRepositoryTestCase
{
    private AttendanceRecordRepository $repository;

    private Registration $registration;

    private AttendanceRecord $attendanceRecord;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AttendanceRecordRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为 count 测试创建测试数据
            $catalogType = new CatalogType();
            $catalogType->setCode('test-' . uniqid());
            $catalogType->setName('测试类型');
            $catalogType->setEnabled(true);
            self::getEntityManager()->persist($catalogType);

            $category = new Catalog();
            $category->setType($catalogType);
            $category->setName('测试分类');
            $category->setEnabled(true);
            self::getEntityManager()->persist($category);

            $course = new Course();
            $course->setTitle('测试课程');
            $course->setDescription('测试课程描述');
            $course->setLearnHour(10);
            $course->setCategory($category);
            self::getEntityManager()->persist($course);

            $classroom = new Classroom();
            $classroom->setTitle('测试教室');
            $classroom->setCategory($category);
            $classroom->setCourse($course);
            self::getEntityManager()->persist($classroom);

            $registration = new Registration();
            $registration->setClassroom($classroom);
            $registration->setStudent($this->createNormalUser());
            self::getEntityManager()->persist($registration);

            $attendanceRecord = new AttendanceRecord();
            $attendanceRecord->setRegistration($registration);
            $attendanceRecord->setAttendanceType(AttendanceType::SIGN_IN);
            $attendanceRecord->setAttendanceTime(new \DateTimeImmutable());
            $attendanceRecord->setAttendanceMethod(AttendanceMethod::QR_CODE);
            $attendanceRecord->setVerificationResult(VerificationResult::SUCCESS);
            $attendanceRecord->setDeviceId('TEST_DEVICE');
            self::getEntityManager()->persist($attendanceRecord);
            self::getEntityManager()->flush();
        } else {
            $this->createTestData();
        }
    }

    private function createTestData(): void
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test-' . uniqid());
        $catalogType->setName('测试类型');
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setType($catalogType);
        $category->setName('测试分类');
        $category->setEnabled(true);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('测试课程');
        $course->setDescription('测试课程描述');
        $course->setLearnHour(10);
        $course->setCategory($category);
        self::getEntityManager()->persist($course);

        $classroom = new Classroom();
        $classroom->setTitle('测试教室');
        $classroom->setCategory($category);
        $classroom->setCourse($course);
        self::getEntityManager()->persist($classroom);

        $this->registration = new Registration();
        $this->registration->setClassroom($classroom);
        $this->registration->setStudent($this->createNormalUser());
        self::getEntityManager()->persist($this->registration);

        $this->attendanceRecord = new AttendanceRecord();
        $this->attendanceRecord->setRegistration($this->registration);
        $this->attendanceRecord->setAttendanceType(AttendanceType::SIGN_IN);
        $this->attendanceRecord->setAttendanceTime(new \DateTimeImmutable());
        $this->attendanceRecord->setAttendanceMethod(AttendanceMethod::QR_CODE);
        $this->attendanceRecord->setVerificationResult(VerificationResult::SUCCESS);
        $this->attendanceRecord->setDeviceId('TEST_DEVICE_001');
        $this->repository->save($this->attendanceRecord);

        self::getEntityManager()->flush();
    }

    public function testFindByRegistration(): void
    {
        $records = $this->repository->findByRegistration($this->registration);

        $this->assertIsArray($records);
        $this->assertCount(1, $records);
        $this->assertInstanceOf(AttendanceRecord::class, $records[0]);
        $this->assertEquals($this->registration->getId(), $records[0]->getRegistration()->getId());
    }

    public function testFindByDateRange(): void
    {
        $today = new \DateTimeImmutable();
        $startDate = $today->modify('-1 day');
        $endDate = $today->modify('+1 day');

        $records = $this->repository->findByDateRange($startDate, $endDate);

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        $this->assertInstanceOf(AttendanceRecord::class, $records[0]);
    }

    public function testFindByType(): void
    {
        $records = $this->repository->findByType(AttendanceType::SIGN_IN);

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        $this->assertEquals(AttendanceType::SIGN_IN, $records[0]->getAttendanceType());
    }

    public function testFindSuccessfulRecords(): void
    {
        $records = $this->repository->findSuccessfulRecords();

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        foreach ($records as $record) {
            $this->assertEquals(VerificationResult::SUCCESS, $record->getVerificationResult());
            $this->assertTrue($record->isValid());
        }
    }

    public function testCountByRegistration(): void
    {
        $count = $this->repository->countByRegistration($this->registration);

        $this->assertIsInt($count);
        $this->assertEquals(1, $count);
    }

    public function testCountSuccessfulByRegistration(): void
    {
        $count = $this->repository->countSuccessfulByRegistration($this->registration);

        $this->assertIsInt($count);
        $this->assertEquals(1, $count);
    }

    public function testFindLatestByRegistration(): void
    {
        $records = $this->repository->findLatestByRegistration($this->registration, 5);

        $this->assertIsArray($records);
        $this->assertCount(1, $records);
        $this->assertEquals($this->registration->getId(), $records[0]->getRegistration()->getId());
    }

    public function testFindByDevice(): void
    {
        $records = $this->repository->findByDevice('TEST_DEVICE_001');

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        $this->assertEquals('TEST_DEVICE_001', $records[0]->getDeviceId());
    }

    public function testGetDailyAttendanceStats(): void
    {
        $today = new \DateTimeImmutable();
        $stats = $this->repository->getDailyAttendanceStats($today);

        $this->assertIsArray($stats);
        if (count($stats) > 0) {
            $this->assertArrayHasKey('attendanceType', $stats[0]);
            $this->assertArrayHasKey('count', $stats[0]);
        }
    }

    public function testFindAnomalousRecords(): void
    {
        $failedRecord = new AttendanceRecord();
        $failedRecord->setRegistration($this->registration);
        $failedRecord->setAttendanceType(AttendanceType::SIGN_OUT);
        $failedRecord->setAttendanceTime(new \DateTimeImmutable());
        $failedRecord->setAttendanceMethod(AttendanceMethod::MANUAL);
        $failedRecord->setVerificationResult(VerificationResult::FAILED);
        $this->repository->save($failedRecord);

        $records = $this->repository->findAnomalousRecords();

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        foreach ($records as $record) {
            $this->assertTrue(
                VerificationResult::SUCCESS !== $record->getVerificationResult() || !$record->isValid()
            );
        }
    }

    public function testFindBySupplier(): void
    {
        $supplierRecord = new AttendanceRecord();
        $supplierRecord->setRegistration($this->registration);
        $supplierRecord->setAttendanceType(AttendanceType::SIGN_IN);
        $supplierRecord->setAttendanceTime(new \DateTimeImmutable());
        $supplierRecord->setAttendanceMethod(AttendanceMethod::FACE);
        $supplierRecord->setVerificationResult(VerificationResult::SUCCESS);
        $supplierRecord->setDeviceId('SUPPLIER_123');
        $this->repository->save($supplierRecord);

        $records = $this->repository->findBySupplier('SUPPLIER_123');

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
    }

    public function testFindByRegistrationAndDateRange(): void
    {
        $today = new \DateTimeImmutable();
        $startDate = $today->modify('-1 day');
        $endDate = $today->modify('+1 day');

        $records = $this->repository->findByRegistrationAndDateRange(
            $this->registration,
            $startDate,
            $endDate
        );

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        $this->assertEquals($this->registration->getId(), $records[0]->getRegistration()->getId());
    }

    public function testFindByRegistrationTypeAndDateRange(): void
    {
        $today = new \DateTimeImmutable();
        $startDate = $today->modify('-1 day');
        $endDate = $today->modify('+1 day');

        $records = $this->repository->findByRegistrationTypeAndDateRange(
            $this->registration,
            AttendanceType::SIGN_IN,
            $startDate,
            $endDate
        );

        $this->assertIsArray($records);
        $this->assertGreaterThanOrEqual(1, count($records));
        $this->assertEquals(AttendanceType::SIGN_IN, $records[0]->getAttendanceType());
    }

    public function testGetCourseAttendanceSummary(): void
    {
        $course = $this->registration->getClassroom()->getCourse();
        $courseId = (int) $course->getId();
        $summary = $this->repository->getCourseAttendanceSummary($courseId);

        $this->assertIsArray($summary);
        if (count($summary) > 0) {
            $this->assertArrayHasKey('registration_id', $summary[0]);
            $this->assertArrayHasKey('total_records', $summary[0]);
            $this->assertArrayHasKey('sign_in_count', $summary[0]);
            $this->assertArrayHasKey('sign_out_count', $summary[0]);
            $this->assertArrayHasKey('attendance_rate', $summary[0]);
        }
    }

    public function testGetAttendanceRateStatistics(): void
    {
        $course = $this->registration->getClassroom()->getCourse();
        $courseId = (int) $course->getId();
        $stats = $this->repository->getAttendanceRateStatistics($courseId);

        $this->assertIsArray($stats);
    }

    public function testSaveAndRemove(): void
    {
        $newRecord = new AttendanceRecord();
        $newRecord->setRegistration($this->registration);
        $newRecord->setAttendanceType(AttendanceType::SIGN_OUT);
        $newRecord->setAttendanceTime(new \DateTimeImmutable());
        $newRecord->setAttendanceMethod(AttendanceMethod::MANUAL);
        $newRecord->setVerificationResult(VerificationResult::SUCCESS);

        $this->repository->save($newRecord);
        self::getEntityManager()->flush();
        $this->assertNotNull($newRecord->getId());

        $recordId = $newRecord->getId();
        $this->repository->remove($newRecord);
        $found = $this->repository->find($recordId);
        $this->assertNull($found);
    }

    public function testFindByWithLimitAndOffsetPagination(): void
    {
        // 创建额外的考勤记录
        for ($i = 1; $i <= 5; ++$i) {
            $record = new AttendanceRecord();
            $record->setRegistration($this->registration);
            $record->setAttendanceType(AttendanceType::SIGN_OUT);
            $record->setAttendanceTime(new \DateTimeImmutable("+{$i} minutes"));
            $record->setAttendanceMethod(AttendanceMethod::MANUAL);
            $record->setVerificationResult(VerificationResult::SUCCESS);
            $record->setDeviceId("DEVICE_{$i}");
            $this->repository->save($record, false);
        }
        self::getEntityManager()->flush();

        // 测试分页
        $results = $this->repository->findBy([], null, 3, 1);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(3, count($results));
    }

    public function testFindOneByWithOrderByCriteria(): void
    {
        // 创建多个记录用于排序测试
        $record1 = new AttendanceRecord();
        $record1->setRegistration($this->registration);
        $record1->setAttendanceType(AttendanceType::SIGN_IN);
        $record1->setAttendanceTime(new \DateTimeImmutable('-1 hour'));
        $record1->setAttendanceMethod(AttendanceMethod::QR_CODE);
        $record1->setVerificationResult(VerificationResult::SUCCESS);
        $record1->setDeviceId('DEVICE_EARLY');
        $this->repository->save($record1, false);

        $record2 = new AttendanceRecord();
        $record2->setRegistration($this->registration);
        $record2->setAttendanceType(AttendanceType::SIGN_IN);
        $record2->setAttendanceTime(new \DateTimeImmutable('+1 hour'));
        $record2->setAttendanceMethod(AttendanceMethod::QR_CODE);
        $record2->setVerificationResult(VerificationResult::SUCCESS);
        $record2->setDeviceId('DEVICE_LATE');
        $this->repository->save($record2, false);

        self::getEntityManager()->flush();

        // 按时间升序查找第一个
        $result = $this->repository->findOneBy(
            ['attendanceType' => AttendanceType::SIGN_IN],
            ['attendanceTime' => 'ASC']
        );

        $this->assertInstanceOf(AttendanceRecord::class, $result);
        $this->assertEquals('DEVICE_EARLY', $result->getDeviceId());
    }

    public function testAssociationQueries(): void
    {
        // 测试通过关联实体查询
        $results = $this->repository->findBy(['registration' => $this->registration]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(AttendanceRecord::class, $result);
            $this->assertEquals($this->registration->getId(), $result->getRegistration()->getId());
        }
    }

    public function testCountAssociationQueries(): void
    {
        $count = $this->repository->count(['registration' => $this->registration]);
        $this->assertGreaterThan(0, $count);

        $foundRecords = $this->repository->findBy(['registration' => $this->registration]);
        $this->assertEquals(count($foundRecords), $count);
    }

    public function testIsNullQueries(): void
    {
        // 创建一个记录，某些可空字段为null
        $nullRecord = new AttendanceRecord();
        $nullRecord->setRegistration($this->registration);
        $nullRecord->setAttendanceType(AttendanceType::SIGN_IN);
        $nullRecord->setAttendanceTime(new \DateTimeImmutable());
        $nullRecord->setAttendanceMethod(AttendanceMethod::MANUAL);
        $nullRecord->setVerificationResult(VerificationResult::SUCCESS);
        // 不设置 deviceId, deviceLocation 等可空字段
        $this->repository->save($nullRecord);

        // 测试查询 null 字段
        $results = $this->repository->findBy(['deviceId' => null]);
        $this->assertIsArray($results);

        $results = $this->repository->findBy(['deviceLocation' => null]);
        $this->assertIsArray($results);
    }

    public function testCountIsNullQueries(): void
    {
        // 测试统计 null 字段
        $count = $this->repository->count(['deviceId' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['deviceLocation' => null]);
        $this->assertIsInt($count);
    }

    public function testFindExpiredRegistrations(): void
    {
        $expiryDate = new \DateTimeImmutable('-30 days');
        $results = $this->repository->findExpiredRegistrations($expiryDate);
        $this->assertIsArray($results);
        // 验证查询条件是基于Registration的createTime字段
        foreach ($results as $record) {
            $this->assertInstanceOf(AttendanceRecord::class, $record);
            $this->assertInstanceOf(Registration::class, $record->getRegistration());
        }
    }

    public function testRemove(): void
    {
        $recordToRemove = new AttendanceRecord();
        $recordToRemove->setRegistration($this->registration);
        $recordToRemove->setAttendanceType(AttendanceType::SIGN_IN);
        $recordToRemove->setAttendanceTime(new \DateTimeImmutable());
        $recordToRemove->setAttendanceMethod(AttendanceMethod::MANUAL);
        $recordToRemove->setVerificationResult(VerificationResult::SUCCESS);
        $recordToRemove->setDeviceId('REMOVE_TEST_DEVICE');
        $this->repository->save($recordToRemove);

        $id = $recordToRemove->getId();
        $this->assertNotNull($id);

        $this->repository->remove($recordToRemove);

        $found = $this->repository->find($id);
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
        self::getEntityManager()->persist($classroom);

        $registration = new Registration();
        $registration->setClassroom($classroom);
        $registration->setStudent($this->createNormalUser());
        self::getEntityManager()->persist($registration);

        $attendanceRecord = new AttendanceRecord();
        $attendanceRecord->setRegistration($registration);
        $attendanceRecord->setAttendanceType(AttendanceType::SIGN_IN);
        $attendanceRecord->setAttendanceTime(new \DateTimeImmutable());
        $attendanceRecord->setAttendanceMethod(AttendanceMethod::QR_CODE);
        $attendanceRecord->setVerificationResult(VerificationResult::SUCCESS);
        $attendanceRecord->setDeviceId('TEST_DEVICE_' . uniqid());

        return $attendanceRecord;
    }

    /**
     * @return ServiceEntityRepository<AttendanceRecord>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
