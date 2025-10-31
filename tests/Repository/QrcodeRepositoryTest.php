<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Qrcode;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(QrcodeRepository::class)]
#[RunTestsInSeparateProcesses]
final class QrcodeRepositoryTest extends AbstractRepositoryTestCase
{
    private QrcodeRepository $repository;

    private Qrcode $qrcode;

    private Classroom $classroom;

    private Catalog $category;

    private Course $course;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(QrcodeRepository::class);

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

            $qrcode = new Qrcode();
            $qrcode->setTitle('测试二维码');
            $qrcode->setClassroom($classroom);
            $qrcode->setLimitNumber(100);
            $qrcode->setValid(true);
            self::getEntityManager()->persist($qrcode);
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

        $this->qrcode = new Qrcode();
        $this->qrcode->setTitle('测试二维码');
        $this->qrcode->setClassroom($this->classroom);
        $this->qrcode->setLimitNumber(100);
        $this->qrcode->setValid(true);
        $this->repository->save($this->qrcode);

        self::getEntityManager()->flush();
    }

    public function testFind(): void
    {
        $found = $this->repository->find($this->qrcode->getId());

        $this->assertInstanceOf(Qrcode::class, $found);
        $this->assertEquals($this->qrcode->getId(), $found->getId());
        $this->assertEquals('测试二维码', $found->getTitle());
    }

    public function testFindAll(): void
    {
        $qrcodes = $this->repository->findAll();

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(1, count($qrcodes));
        $this->assertInstanceOf(Qrcode::class, $qrcodes[0]);
    }

    public function testFindBy(): void
    {
        $qrcodes = $this->repository->findBy(['valid' => true]);

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(1, count($qrcodes));
        foreach ($qrcodes as $qrcode) {
            $this->assertTrue($qrcode->isValid());
        }
    }

    public function testFindByWithOrder(): void
    {
        $qrcode2 = new Qrcode();
        $qrcode2->setTitle('另一个测试二维码');
        $qrcode2->setClassroom($this->classroom);
        $qrcode2->setLimitNumber(50);
        $qrcode2->setValid(false);
        $this->repository->save($qrcode2);

        $qrcodes = $this->repository->findBy(
            ['classroom' => $this->classroom],
            ['title' => 'ASC']
        );

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(2, count($qrcodes));

        // 验证排序是否正确（按字母顺序）
        $titles = array_map(fn ($q) => $q->getTitle(), $qrcodes);
        $sortedTitles = $titles;
        /** @var array<string> $sortedTitles */
        sort($sortedTitles);
        $this->assertEquals($sortedTitles, $titles);

        // 验证我们创建的二维码是否存在
        $this->assertContains('另一个测试二维码', $titles);
        $this->assertContains('测试二维码', $titles);
    }

    public function testFindByWithLimitAndOffset(): void
    {
        $qrcode2 = new Qrcode();
        $qrcode2->setTitle('第二个二维码');
        $qrcode2->setClassroom($this->classroom);
        $qrcode2->setLimitNumber(75);
        $qrcode2->setValid(true);
        $this->repository->save($qrcode2);

        $qrcode3 = new Qrcode();
        $qrcode3->setTitle('第三个二维码');
        $qrcode3->setClassroom($this->classroom);
        $qrcode3->setLimitNumber(25);
        $qrcode3->setValid(true);
        $this->repository->save($qrcode3);

        $qrcodes = $this->repository->findBy(
            ['valid' => true],
            ['title' => 'ASC'],
            2,
            1
        );

        $this->assertIsArray($qrcodes);
        $this->assertCount(2, $qrcodes);
    }

    public function testFindOneBy(): void
    {
        $qrcode = $this->repository->findOneBy(['title' => '测试二维码']);

        $this->assertInstanceOf(Qrcode::class, $qrcode);
        $this->assertEquals('测试二维码', $qrcode->getTitle());
        $this->assertEquals(100, $qrcode->getLimitNumber());
    }

    public function testFindOneByNotFound(): void
    {
        $qrcode = $this->repository->findOneBy(['title' => '不存在的二维码']);

        $this->assertNull($qrcode);
    }

    public function testFindByClassroom(): void
    {
        $qrcodes = $this->repository->findBy(['classroom' => $this->classroom]);

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(1, count($qrcodes));
        foreach ($qrcodes as $qrcode) {
            $this->assertEquals($this->classroom->getId(), $qrcode->getClassroom()->getId());
        }
    }

    public function testFindByValidStatus(): void
    {
        $invalidQrcode = new Qrcode();
        $invalidQrcode->setTitle('无效二维码');
        $invalidQrcode->setClassroom($this->classroom);
        $invalidQrcode->setLimitNumber(10);
        $invalidQrcode->setValid(false);
        $this->repository->save($invalidQrcode);

        $validQrcodes = $this->repository->findBy(['valid' => true]);
        $invalidQrcodes = $this->repository->findBy(['valid' => false]);

        $this->assertIsArray($validQrcodes);
        $this->assertIsArray($invalidQrcodes);
        $this->assertGreaterThanOrEqual(1, count($validQrcodes));
        $this->assertGreaterThanOrEqual(1, count($invalidQrcodes));

        foreach ($validQrcodes as $qrcode) {
            $this->assertTrue($qrcode->isValid());
        }

        foreach ($invalidQrcodes as $qrcode) {
            $this->assertFalse($qrcode->isValid());
        }
    }

    public function testFindByLimitNumber(): void
    {
        $qrcodes = $this->repository->findBy(['limitNumber' => 100]);

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(1, count($qrcodes));
        foreach ($qrcodes as $qrcode) {
            $this->assertEquals(100, $qrcode->getLimitNumber());
        }
    }

    public function testSaveAndFlush(): void
    {
        $newQrcode = new Qrcode();
        $newQrcode->setTitle('新二维码');
        $newQrcode->setClassroom($this->classroom);
        $newQrcode->setLimitNumber(200);
        $newQrcode->setValid(true);

        $this->repository->save($newQrcode, true);

        $this->assertNotNull($newQrcode->getId());

        $found = $this->repository->find($newQrcode->getId());
        $this->assertInstanceOf(Qrcode::class, $found);
        $this->assertEquals('新二维码', $found->getTitle());
    }

    public function testSaveWithoutFlush(): void
    {
        $newQrcode = new Qrcode();
        $newQrcode->setTitle('延迟保存二维码');
        $newQrcode->setClassroom($this->classroom);
        $newQrcode->setLimitNumber(150);
        $newQrcode->setValid(false);

        $this->repository->save($newQrcode, false);
        self::getEntityManager()->flush();

        $this->assertNotNull($newQrcode->getId());

        $found = $this->repository->find($newQrcode->getId());
        $this->assertInstanceOf(Qrcode::class, $found);
        $this->assertEquals('延迟保存二维码', $found->getTitle());
    }

    public function testRemove(): void
    {
        $qrcodeToRemove = new Qrcode();
        $qrcodeToRemove->setTitle('待删除二维码');
        $qrcodeToRemove->setClassroom($this->classroom);
        $qrcodeToRemove->setLimitNumber(30);
        $qrcodeToRemove->setValid(true);
        $this->repository->save($qrcodeToRemove);

        $id = $qrcodeToRemove->getId();
        $this->assertNotNull($id);

        $this->repository->remove($qrcodeToRemove);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindByMultipleCriteria(): void
    {
        $specialQrcode = new Qrcode();
        $specialQrcode->setTitle('特殊二维码');
        $specialQrcode->setClassroom($this->classroom);
        $specialQrcode->setLimitNumber(500);
        $specialQrcode->setValid(true);
        $this->repository->save($specialQrcode);

        $qrcodes = $this->repository->findBy([
            'classroom' => $this->classroom,
            'valid' => true,
            'limitNumber' => 500,
        ]);

        $this->assertIsArray($qrcodes);
        $this->assertGreaterThanOrEqual(1, count($qrcodes));
        foreach ($qrcodes as $qrcode) {
            $this->assertEquals($this->classroom->getId(), $qrcode->getClassroom()->getId());
            $this->assertTrue($qrcode->isValid());
            $this->assertEquals(500, $qrcode->getLimitNumber());
        }
    }

    public function testEntityProperties(): void
    {
        $this->assertEquals('测试二维码', $this->qrcode->getTitle());
        $this->assertEquals(100, $this->qrcode->getLimitNumber());
        $this->assertTrue($this->qrcode->isValid());
        $this->assertEquals($this->classroom->getId(), $this->qrcode->getClassroom()->getId());
        $this->assertInstanceOf(Collection::class, $this->qrcode->getRegistrations());
    }

    public function testQrcodeStringRepresentation(): void
    {
        $string = (string) $this->qrcode;
        $this->assertIsString($string);
        $this->assertEquals((string) $this->qrcode->getId(), $string);
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

        $qrcode = new Qrcode();
        $qrcode->setTitle('测试二维码 ' . uniqid());
        $qrcode->setClassroom($classroom);
        $qrcode->setLimitNumber(50);
        $qrcode->setValid(true);

        return $qrcode;
    }

    /**
     * @return ServiceEntityRepository<Qrcode>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }
}
