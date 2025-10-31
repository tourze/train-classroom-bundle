<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use BizUserBundle\DataFixtures\BizUserFixtures;
use BizUserBundle\Entity\BizUser;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainCourseBundle\Entity\Course;

class RegistrationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REGISTRATION_REFERENCE = 'registration-reference';

    public static function getGroups(): array
    {
        return ['test', 'dev', 'production'];
    }

    public function load(ObjectManager $manager): void
    {
        // 获取依赖的实体
        $classroom = $this->getReference(ClassroomFixtures::CLASSROOM_REFERENCE, Classroom::class);
        $course = $this->getReference(ClassroomFixtures::COURSE_REFERENCE, Course::class);
        $student = $this->getReference(BizUserFixtures::ADMIN_USER_REFERENCE, BizUser::class);

        // 创建测试用注册记录
        $registration = new Registration();
        $registration->setClassroom($classroom);
        $registration->setCourse($course);
        $registration->setStudent($student);
        $registration->setStatus(OrderStatus::PAID);
        $registration->setBeginTime(new \DateTimeImmutable('-1 month'));
        $registration->setEndTime(new \DateTimeImmutable('+11 months'));
        $registration->setPayTime(new \DateTimeImmutable('-1 month'));
        $registration->setPayPrice('199.00');

        $manager->persist($registration);
        $this->addReference(self::REGISTRATION_REFERENCE, $registration);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClassroomFixtures::class,
            BizUserFixtures::class,
        ];
    }
}
