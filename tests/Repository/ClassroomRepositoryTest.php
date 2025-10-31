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
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(ClassroomRepository::class)]
#[RunTestsInSeparateProcesses]
final class ClassroomRepositoryTest extends AbstractRepositoryTestCase
{
    private ClassroomRepository $repository;

    private Classroom $classroom;

    private Catalog $category;

    private Course $course;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(ClassroomRepository::class);

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
            $classroom->setCapacity(50);
            self::getEntityManager()->persist($classroom);
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
        $this->classroom->setTitle('测试教室A');
        $this->classroom->setCategory($this->category);
        $this->classroom->setCourse($this->course);
        $this->classroom->setType('PHYSICAL');
        $this->classroom->setStatus('ACTIVE');
        $this->classroom->setCapacity(50);
        $this->classroom->setLocation('一楼101教室');
        $this->classroom->setDescription('测试教室描述');
        $this->repository->save($this->classroom);

        self::getEntityManager()->flush();
    }

    public function testFind(): void
    {
        $found = $this->repository->find($this->classroom->getId());

        $this->assertInstanceOf(Classroom::class, $found);
        $this->assertEquals($this->classroom->getId(), $found->getId());
        $this->assertEquals('测试教室A', $found->getTitle());
    }

    public function testFindAll(): void
    {
        $classrooms = $this->repository->findAll();

        $this->assertIsArray($classrooms);
        $this->assertGreaterThanOrEqual(1, count($classrooms));
        $this->assertInstanceOf(Classroom::class, $classrooms[0]);
    }

    public function testFindBy(): void
    {
        $classrooms = $this->repository->findBy(['type' => 'PHYSICAL']);

        $this->assertIsArray($classrooms);
        $this->assertGreaterThanOrEqual(1, count($classrooms));
        foreach ($classrooms as $classroom) {
            $this->assertEquals('PHYSICAL', $classroom->getType());
        }
    }

    public function testFindByWithOrder(): void
    {
        $classroom2 = new Classroom();
        $classroom2->setTitle('测试教室B');
        $classroom2->setCategory($this->category);
        $classroom2->setCourse($this->course);
        $classroom2->setType('VIRTUAL');
        $classroom2->setStatus('ACTIVE');
        $this->repository->save($classroom2);

        $classrooms = $this->repository->findBy(
            ['status' => 'ACTIVE'],
            ['title' => 'ASC']
        );

        $this->assertIsArray($classrooms);
        $this->assertGreaterThanOrEqual(2, count($classrooms));
        $this->assertEquals('测试教室A', $classrooms[0]->getTitle());
        $this->assertEquals('测试教室B', $classrooms[1]->getTitle());
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $classroom2 = new Classroom();
        $classroom2->setTitle('测试教室C');
        $classroom2->setCategory($this->category);
        $classroom2->setCourse($this->course);
        $classroom2->setType('HYBRID');
        $classroom2->setStatus('ACTIVE');
        $this->repository->save($classroom2);

        $classrooms = $this->repository->findBy(
            ['status' => 'ACTIVE'],
            ['title' => 'ASC'],
            1,
            0
        );

        $this->assertIsArray($classrooms);
        $this->assertCount(1, $classrooms);
    }

    public function testFindOneBy(): void
    {
        $classroom = $this->repository->findOneBy(['title' => '测试教室A']);

        $this->assertInstanceOf(Classroom::class, $classroom);
        $this->assertEquals('测试教室A', $classroom->getTitle());
        $this->assertEquals('PHYSICAL', $classroom->getType());
    }

    public function testFindOneByNotFound(): void
    {
        $classroom = $this->repository->findOneBy(['title' => '不存在的教室']);

        $this->assertNull($classroom);
    }

    public function testSaveAndFlush(): void
    {
        $newClassroom = new Classroom();
        $newClassroom->setTitle('新教室');
        $newClassroom->setCategory($this->category);
        $newClassroom->setCourse($this->course);
        $newClassroom->setType('VIRTUAL');
        $newClassroom->setStatus('INACTIVE');

        $this->repository->save($newClassroom, true);

        $this->assertNotNull($newClassroom->getId());

        $found = $this->repository->find($newClassroom->getId());
        $this->assertInstanceOf(Classroom::class, $found);
        $this->assertEquals('新教室', $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $newClassroom = new Classroom();
        $newClassroom->setTitle('延迟保存教室');
        $newClassroom->setCategory($this->category);
        $newClassroom->setCourse($this->course);
        $newClassroom->setType('PHYSICAL');
        $newClassroom->setStatus('MAINTENANCE');

        $this->repository->save($newClassroom, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($newClassroom->getId());

        $found = $this->repository->find($newClassroom->getId());
        $this->assertInstanceOf(Classroom::class, $found);
    }

    public function testRemove(): void
    {
        $classroomToRemove = new Classroom();
        $classroomToRemove->setTitle('待删除教室');
        $classroomToRemove->setCategory($this->category);
        $classroomToRemove->setCourse($this->course);
        $classroomToRemove->setType('PHYSICAL');
        $classroomToRemove->setStatus('INACTIVE');
        $this->repository->save($classroomToRemove);

        $id = $classroomToRemove->getId();
        $this->assertNotNull($id);

        $this->repository->remove($classroomToRemove);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByMultipleCriteria(): void
    {
        $classroom2 = new Classroom();
        $classroom2->setTitle('特殊教室');
        $classroom2->setCategory($this->category);
        $classroom2->setCourse($this->course);
        $classroom2->setType('PHYSICAL');
        $classroom2->setStatus('MAINTENANCE');
        $classroom2->setCapacity(30);
        $this->repository->save($classroom2);

        $classrooms = $this->repository->findBy([
            'type' => 'PHYSICAL',
            'status' => 'MAINTENANCE',
        ]);

        $this->assertIsArray($classrooms);
        $this->assertGreaterThanOrEqual(1, count($classrooms));
        foreach ($classrooms as $classroom) {
            $this->assertEquals('PHYSICAL', $classroom->getType());
            $this->assertEquals('MAINTENANCE', $classroom->getStatus());
        }
    }

    public function testFindOneByWithOrderByCriteria(): void
    {
        // 创建多个教室用于排序测试
        $classroom1 = new Classroom();
        $classroom1->setTitle('AAA教室排序');
        $classroom1->setCategory($this->category);
        $classroom1->setCourse($this->course);
        $classroom1->setType('VIRTUAL');
        $classroom1->setStatus('ACTIVE');
        $classroom1->setCapacity(10);
        $this->repository->save($classroom1, false);

        $classroom2 = new Classroom();
        $classroom2->setTitle('ZZZ教室排序');
        $classroom2->setCategory($this->category);
        $classroom2->setCourse($this->course);
        $classroom2->setType('VIRTUAL');
        $classroom2->setStatus('ACTIVE');
        $classroom2->setCapacity(20);
        $this->repository->save($classroom2, false);

        self::getEntityManager()->flush();

        // 按标题升序查找第一个
        $result = $this->repository->findOneBy(
            ['status' => 'ACTIVE'],
            ['title' => 'ASC']
        );

        $this->assertInstanceOf(Classroom::class, $result);
        $this->assertEquals('AAA教室排序', $result->getTitle());
    }

    public function testAssociationQueries(): void
    {
        // 测试通过关联实体查询
        $results = $this->repository->findBy(['category' => $this->category]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(Classroom::class, $result);
            $this->assertEquals($this->category->getId(), $result->getCategory()->getId());
        }

        $results = $this->repository->findBy(['course' => $this->course]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        foreach ($results as $result) {
            $this->assertInstanceOf(Classroom::class, $result);
            $this->assertEquals($this->course->getId(), $result->getCourse()->getId());
        }
    }

    public function testCountAssociationQueries(): void
    {
        $count = $this->repository->count(['category' => $this->category]);
        $this->assertGreaterThan(0, $count);

        $foundRecords = $this->repository->findBy(['category' => $this->category]);
        $this->assertEquals(count($foundRecords), $count);

        $count = $this->repository->count(['course' => $this->course]);
        $this->assertGreaterThan(0, $count);

        $foundRecords = $this->repository->findBy(['course' => $this->course]);
        $this->assertEquals(count($foundRecords), $count);
    }

    public function testIsNullQueries(): void
    {
        // 创建一个教室，某些可空字段为null
        $nullClassroom = new Classroom();
        $nullClassroom->setTitle('空字段测试教室');
        $nullClassroom->setType('VIRTUAL');
        $nullClassroom->setStatus('ACTIVE');
        $nullClassroom->setCapacity(25);
        // 设置必需的 category 和 course 字段
        $nullClassroom->setCategory($this->category);
        $nullClassroom->setCourse($this->course);
        // 不设置其他可空字段如 location, description 等
        $this->repository->save($nullClassroom);

        // 测试查询 null 字段（只测试可空字段）
        $results = $this->repository->findBy(['location' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results)); // 应该能找到刚创建的没有location的教室

        $results = $this->repository->findBy(['description' => null]);
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results)); // 应该能找到刚创建的没有description的教室

        $results = $this->repository->findBy(['startTime' => null]);
        $this->assertIsArray($results);

        $results = $this->repository->findBy(['endTime' => null]);
        $this->assertIsArray($results);
    }

    public function testCountIsNullQueries(): void
    {
        // 测试统计 null 字段
        $count = $this->repository->count(['location' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['description' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['area' => null]);
        $this->assertIsInt($count);

        $count = $this->repository->count(['devices' => null]);
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
        $classroom->setCapacity(30);

        return $classroom;
    }

    /**
     * @return ServiceEntityRepository<Classroom>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
