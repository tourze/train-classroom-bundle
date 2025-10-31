<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainCourseBundle\Entity\Course;

#[When(env: 'test')]
#[When(env: 'dev')]
class ClassroomFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const CLASSROOM_REFERENCE = 'classroom-test';
    public const COURSE_REFERENCE = 'course-test';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        // 创建测试用课程
        $course = new Course();
        $course->setTitle('测试课程');
        $course->setDescription('这是一个测试课程');
        $course->setCategory($this->getReference(TrainingCatalogFixtures::MAIN_RESPONSIBLE_PERSON_REFERENCE, Catalog::class));
        $course->setLearnHour(40);
        $manager->persist($course);
        $this->addReference(self::COURSE_REFERENCE, $course);

        // 创建测试用教室
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');
        $classroom->setCategory($this->getReference(TrainingCatalogFixtures::MAIN_RESPONSIBLE_PERSON_REFERENCE, Catalog::class));
        $classroom->setCourse($course);
        $classroom->setStartTime(new \DateTimeImmutable('2024-01-01'));
        $classroom->setEndTime(new \DateTimeImmutable('2024-12-31'));
        $manager->persist($classroom);
        $this->addReference(self::CLASSROOM_REFERENCE, $classroom);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            TrainingCatalogFixtures::class,
        ];
    }
}
