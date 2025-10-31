<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(RegistrationRepository::class)]
#[RunTestsInSeparateProcesses]
final class RegistrationRepositoryTest extends AbstractRepositoryTestCase
{
    private RegistrationRepository $repository;

    private Registration $registration;

    private Classroom $classroom;

    private Catalog $category;

    private Course $course;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(RegistrationRepository::class);

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
            $classroom->setType('PHYSICAL');
            $classroom->setStatus('ACTIVE');
            self::getEntityManager()->persist($classroom);

            $registration = new Registration();
            $registration->setClassroom($classroom);
            $registration->setStudent($this->createNormalUser());
            $registration->setTrainType(TrainType::ONLINE);
            $registration->setStatus(OrderStatus::PENDING);
            $registration->setBeginTime(new \DateTimeImmutable());
            $registration->setAge(25);
            self::getEntityManager()->persist($registration);
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

        $mockUser = $this->createMockUser();

        $this->registration = new Registration();
        $this->registration->setClassroom($this->classroom);
        $this->registration->setStudent($mockUser);
        $this->registration->setTrainType(TrainType::ONLINE);
        $this->registration->setStatus(OrderStatus::PENDING);
        $this->registration->setBeginTime(new \DateTimeImmutable());
        $this->registration->setEndTime(new \DateTimeImmutable('+30 days'));
        $this->registration->setAge(25);
        $this->registration->setPayPrice('199.00');
        $this->repository->save($this->registration);

        self::getEntityManager()->flush();
    }

    public function testFind(): void
    {
        $found = $this->repository->find($this->registration->getId());

        $this->assertInstanceOf(Registration::class, $found);
        $this->assertEquals($this->registration->getId(), $found->getId());
        $this->assertEquals(TrainType::ONLINE, $found->getTrainType());
    }

    public function testFindAll(): void
    {
        $registrations = $this->repository->findAll();

        $this->assertIsArray($registrations);
        $this->assertGreaterThanOrEqual(1, count($registrations));
        $this->assertInstanceOf(Registration::class, $registrations[0]);
    }

    public function testFindBy(): void
    {
        $registrations = $this->repository->findBy(['status' => OrderStatus::PENDING]);

        $this->assertIsArray($registrations);
        $this->assertGreaterThanOrEqual(1, count($registrations));
        foreach ($registrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals(OrderStatus::PENDING, $registration->getStatus());
        }
    }

    public function testFindByWithOrder(): void
    {
        $registration2 = new Registration();
        $registration2->setClassroom($this->classroom);
        $registration2->setStudent($this->createMockUser('user2'));
        $registration2->setTrainType(TrainType::OFFLINE);
        $registration2->setStatus(OrderStatus::PAID);
        $registration2->setBeginTime(new \DateTimeImmutable('+1 day'));
        $registration2->setAge(30);
        $this->repository->save($registration2);

        // 确保数据已保存到数据库
        self::getEntityManager()->flush();

        // 查询有age值的记录并按age排序
        $registrations = $this->repository->findBy(
            ['age' => [25, 30]], // 只查询我们创建的记录
            ['age' => 'ASC']
        );

        $this->assertIsArray($registrations);
        $this->assertCount(2, $registrations);
        $this->assertInstanceOf(Registration::class, $registrations[0]);
        $this->assertEquals(25, $registrations[0]->getAge());
        $this->assertInstanceOf(Registration::class, $registrations[1]);
        $this->assertEquals(30, $registrations[1]->getAge());
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $registration2 = new Registration();
        $registration2->setClassroom($this->classroom);
        $registration2->setStudent($this->createMockUser('user2'));
        $registration2->setTrainType(TrainType::HYBRID);
        $registration2->setStatus(OrderStatus::PAID);
        $registration2->setBeginTime(new \DateTimeImmutable('+2 days'));
        $this->repository->save($registration2);

        $registration3 = new Registration();
        $registration3->setClassroom($this->classroom);
        $registration3->setStudent($this->createMockUser('user3'));
        $registration3->setTrainType(TrainType::ONLINE);
        $registration3->setStatus(OrderStatus::CANCELLED);
        $registration3->setBeginTime(new \DateTimeImmutable('+3 days'));
        $this->repository->save($registration3);

        $registrations = $this->repository->findBy(
            [],
            ['id' => 'ASC'],
            2,
            1
        );

        $this->assertIsArray($registrations);
        $this->assertCount(2, $registrations);
    }

    public function testFindOneBy(): void
    {
        $registration = $this->repository->findOneBy(['trainType' => TrainType::ONLINE]);

        $this->assertInstanceOf(Registration::class, $registration);
        $this->assertEquals(TrainType::ONLINE, $registration->getTrainType());
        $this->assertEquals(25, $registration->getAge());
    }

    public function testFindOneByNotFound(): void
    {
        $registration = $this->repository->findOneBy(['age' => 999]);

        $this->assertNull($registration);
    }

    public function testFindByClassroom(): void
    {
        $registrations = $this->repository->findBy(['classroom' => $this->classroom]);

        $this->assertIsArray($registrations);
        $this->assertGreaterThanOrEqual(1, count($registrations));
        foreach ($registrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals($this->classroom->getId(), $registration->getClassroom()->getId());
        }
    }

    public function testFindByStatus(): void
    {
        $pendingRegistrations = $this->repository->findBy(['status' => OrderStatus::PENDING]);

        $this->assertIsArray($pendingRegistrations);
        $this->assertGreaterThanOrEqual(1, count($pendingRegistrations));
        foreach ($pendingRegistrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals(OrderStatus::PENDING, $registration->getStatus());
        }
    }

    public function testFindByTrainType(): void
    {
        $onlineRegistrations = $this->repository->findBy(['trainType' => TrainType::ONLINE]);

        $this->assertIsArray($onlineRegistrations);
        $this->assertGreaterThanOrEqual(1, count($onlineRegistrations));
        foreach ($onlineRegistrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals(TrainType::ONLINE, $registration->getTrainType());
        }
    }

    public function testFindByFinished(): void
    {
        $unfinishedRegistration = new Registration();
        $unfinishedRegistration->setClassroom($this->classroom);
        $unfinishedRegistration->setStudent($this->createMockUser('unfinished'));
        $unfinishedRegistration->setTrainType(TrainType::OFFLINE);
        $unfinishedRegistration->setStatus(OrderStatus::PENDING);
        $unfinishedRegistration->setBeginTime(new \DateTimeImmutable());
        $unfinishedRegistration->setFinished(false);
        $this->repository->save($unfinishedRegistration);

        $finishedRegistration = new Registration();
        $finishedRegistration->setClassroom($this->classroom);
        $finishedRegistration->setStudent($this->createMockUser('finished'));
        $finishedRegistration->setTrainType(TrainType::ONLINE);
        $finishedRegistration->setStatus(OrderStatus::PAID);
        $finishedRegistration->setBeginTime(new \DateTimeImmutable('-10 days'));
        $finishedRegistration->setFinished(true);
        $finishedRegistration->setFinishTime(new \DateTimeImmutable('-1 day'));
        $this->repository->save($finishedRegistration);

        $unfinishedRegistrations = $this->repository->findBy(['finished' => false]);
        $finishedRegistrations = $this->repository->findBy(['finished' => true]);

        $this->assertIsArray($unfinishedRegistrations);
        $this->assertIsArray($finishedRegistrations);
        $this->assertGreaterThanOrEqual(1, count($unfinishedRegistrations));
        $this->assertGreaterThanOrEqual(1, count($finishedRegistrations));

        foreach ($unfinishedRegistrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertFalse($registration->isFinished());
        }

        foreach ($finishedRegistrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertTrue($registration->isFinished());
        }
    }

    public function testFindByExpiredStatus(): void
    {
        $expiredRegistration = new Registration();
        $expiredRegistration->setClassroom($this->classroom);
        $expiredRegistration->setStudent($this->createMockUser('expired'));
        $expiredRegistration->setTrainType(TrainType::HYBRID);
        $expiredRegistration->setStatus(OrderStatus::CANCELLED);
        $expiredRegistration->setBeginTime(new \DateTimeImmutable('-60 days'));
        $expiredRegistration->setEndTime(new \DateTimeImmutable('-30 days'));
        $expiredRegistration->setExpired(true);
        $this->repository->save($expiredRegistration);

        $expiredRegistrations = $this->repository->findBy(['expired' => true]);

        $this->assertIsArray($expiredRegistrations);
        $this->assertGreaterThanOrEqual(1, count($expiredRegistrations));
        foreach ($expiredRegistrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertTrue($registration->isExpired());
        }
    }

    public function testFindByPayPrice(): void
    {
        $paidRegistration = new Registration();
        $paidRegistration->setClassroom($this->classroom);
        $paidRegistration->setStudent($this->createMockUser('paid'));
        $paidRegistration->setTrainType(TrainType::ONLINE);
        $paidRegistration->setStatus(OrderStatus::PAID);
        $paidRegistration->setBeginTime(new \DateTimeImmutable());
        $paidRegistration->setPayPrice('299.00');
        $paidRegistration->setPayTime(new \DateTimeImmutable());
        $this->repository->save($paidRegistration);

        $registrations = $this->repository->findBy(['payPrice' => '299.00']);

        $this->assertIsArray($registrations);
        $this->assertGreaterThanOrEqual(1, count($registrations));
        foreach ($registrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals('299.00', $registration->getPayPrice());
        }
    }

    public function testSaveAndFlush(): void
    {
        $newRegistration = new Registration();
        $newRegistration->setClassroom($this->classroom);
        $newRegistration->setStudent($this->createMockUser('new'));
        $newRegistration->setTrainType(TrainType::OFFLINE);
        $newRegistration->setStatus(OrderStatus::PENDING);
        $newRegistration->setBeginTime(new \DateTimeImmutable('+1 week'));

        $this->repository->save($newRegistration, true);

        $this->assertNotNull($newRegistration->getId());

        $found = $this->repository->find($newRegistration->getId());
        $this->assertInstanceOf(Registration::class, $found);
        $this->assertEquals(TrainType::OFFLINE, $found->getTrainType());
    }

    public function testSaveWithoutFlush(): void
    {
        $newRegistration = new Registration();
        $newRegistration->setClassroom($this->classroom);
        $newRegistration->setStudent($this->createMockUser('delayed'));
        $newRegistration->setTrainType(TrainType::HYBRID);
        $newRegistration->setStatus(OrderStatus::PENDING);
        $newRegistration->setBeginTime(new \DateTimeImmutable('+2 weeks'));

        $this->repository->save($newRegistration, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($newRegistration->getId());

        $found = $this->repository->find($newRegistration->getId());
        $this->assertInstanceOf(Registration::class, $found);
        $this->assertEquals(TrainType::HYBRID, $found->getTrainType());
    }

    public function testRemove(): void
    {
        $registrationToRemove = new Registration();
        $registrationToRemove->setClassroom($this->classroom);
        $registrationToRemove->setStudent($this->createMockUser('remove'));
        $registrationToRemove->setTrainType(TrainType::ONLINE);
        $registrationToRemove->setStatus(OrderStatus::CANCELLED);
        $registrationToRemove->setBeginTime(new \DateTimeImmutable());
        $this->repository->save($registrationToRemove);

        $id = $registrationToRemove->getId();
        $this->assertNotNull($id);

        $this->repository->remove($registrationToRemove);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByMultipleCriteria(): void
    {
        $specialRegistration = new Registration();
        $specialRegistration->setClassroom($this->classroom);
        $specialRegistration->setStudent($this->createMockUser('special'));
        $specialRegistration->setTrainType(TrainType::ONLINE);
        $specialRegistration->setStatus(OrderStatus::PAID);
        $specialRegistration->setBeginTime(new \DateTimeImmutable());
        $specialRegistration->setAge(35);
        $specialRegistration->setPayPrice('399.00');
        $this->repository->save($specialRegistration);

        $registrations = $this->repository->findBy([
            'trainType' => TrainType::ONLINE,
            'status' => OrderStatus::PAID,
            'age' => 35,
        ]);

        $this->assertIsArray($registrations);
        $this->assertGreaterThanOrEqual(1, count($registrations));
        foreach ($registrations as $registration) {
            $this->assertInstanceOf(Registration::class, $registration);
            $this->assertEquals(TrainType::ONLINE, $registration->getTrainType());
            $this->assertEquals(OrderStatus::PAID, $registration->getStatus());
            $this->assertEquals(35, $registration->getAge());
        }
    }

    public function testRegistrationEntityMethods(): void
    {
        $this->assertEquals($this->classroom->getId(), $this->registration->getClassroom()->getId());
        $this->assertEquals(TrainType::ONLINE, $this->registration->getTrainType());
        $this->assertEquals(OrderStatus::PENDING, $this->registration->getStatus());
        $this->assertEquals(25, $this->registration->getAge());
        $this->assertEquals('199.00', $this->registration->getPayPrice());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->registration->getBeginTime());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->registration->getEndTime());
        $this->assertInstanceOf(Collection::class, $this->registration->getAttendanceRecords());
        $this->assertTrue($this->registration->isActive());
    }

    public function testRegistrationStringRepresentation(): void
    {
        $string = (string) $this->registration;
        $this->assertIsString($string);
        $this->assertStringContainsString($this->classroom->getTitle(), $string);
        $this->assertStringContainsString('报名于', $string);
    }

    private function createMockUser(string $identifier = 'test_user'): BizUser
    {
        /** @var int $counter */
        static $counter = 1;

        $user = new BizUser();
        $user->setUsername($identifier . '_' . ((string) $counter++));
        $user->setNickName($identifier);
        $user->setPasswordHash('password_hash');

        self::getEntityManager()->persist($user);

        return $user;
    }

    public function testFindOneByWithOrderByCriteria(): void
    {
        // 创建多个报名记录用于排序测试
        $registration1 = new Registration();
        $registration1->setClassroom($this->classroom);
        $registration1->setStudent($this->createMockUser('user_sort1'));
        $registration1->setTrainType(TrainType::ONLINE);
        $registration1->setStatus(OrderStatus::PENDING);
        $registration1->setBeginTime(new \DateTimeImmutable('-1 day'));
        $registration1->setAge(18);
        $this->repository->save($registration1, false);

        $registration2 = new Registration();
        $registration2->setClassroom($this->classroom);
        $registration2->setStudent($this->createMockUser('user_sort2'));
        $registration2->setTrainType(TrainType::ONLINE);
        $registration2->setStatus(OrderStatus::PENDING);
        $registration2->setBeginTime(new \DateTimeImmutable('+1 day'));
        $registration2->setAge(35);
        $this->repository->save($registration2, false);

        self::getEntityManager()->flush();

        // 按年龄升序查找第一个
        $result = $this->repository->findOneBy(
            ['status' => OrderStatus::PENDING],
            ['age' => 'ASC']
        );

        $this->assertInstanceOf(Registration::class, $result);
        $this->assertEquals(18, $result->getAge());
    }

    public function testAssociationQueries(): void
    {
        // 测试通过关联实体查询
        $results = $this->repository->findBy(['classroom' => $this->classroom]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(Registration::class, $result);
            $this->assertEquals($this->classroom->getId(), $result->getClassroom()->getId());
        }
    }

    public function testCountAssociationQueries(): void
    {
        $count = $this->repository->count(['classroom' => $this->classroom]);
        $this->assertGreaterThan(0, $count);

        $foundRecords = $this->repository->findBy(['classroom' => $this->classroom]);
        $this->assertEquals(count($foundRecords), $count);
    }

    public function testIsNullQueries(): void
    {
        // 创建一个报名记录，某些可空字段为null
        $nullRegistration = new Registration();
        $nullRegistration->setClassroom($this->classroom);
        $nullRegistration->setStudent($this->createMockUser('null_test'));
        $nullRegistration->setTrainType(TrainType::ONLINE);
        $nullRegistration->setStatus(OrderStatus::PENDING);
        $nullRegistration->setBeginTime(new \DateTimeImmutable());
        // 不设置 endTime, payPrice 等可空字段
        $this->repository->save($nullRegistration);

        // 测试查询 null 字段
        $results = $this->repository->findBy(['endTime' => null]);
        $this->assertIsArray($results);

        $results = $this->repository->findBy(['payPrice' => null]);
        $this->assertIsArray($results);

        $results = $this->repository->findBy(['payTime' => null]);
        $this->assertIsArray($results);

        $results = $this->repository->findBy(['finishTime' => null]);
        $this->assertIsArray($results);
    }

    public function testCountIsNullQueries(): void
    {
        // 测试统计 null 字段
        $count = $this->repository->count(['endTime' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['payPrice' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['payTime' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['finishTime' => null]);
        $this->assertIsInt($count);
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
        $classroom->setType('VIRTUAL');
        $classroom->setStatus('ACTIVE');
        self::getEntityManager()->persist($classroom);

        $registration = new Registration();
        $registration->setClassroom($classroom);
        $registration->setStudent($this->createNormalUser());
        $registration->setTrainType(TrainType::ONLINE);
        $registration->setStatus(OrderStatus::PENDING);
        $registration->setBeginTime(new \DateTimeImmutable());
        $registration->setAge(25);

        return $registration;
    }

    /**
     * @return ServiceEntityRepository<Registration>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
