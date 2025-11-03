<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainCourseBundle\Entity\Course;
use Tourze\UserServiceContracts\UserManagerInterface;

class RegistrationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const REGISTRATION_REFERENCE = 'registration-reference';

    public function __construct(
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public static function getGroups(): array
    {
        return ['dev', 'production']; // 暂时从test组中移除以避免依赖问题
    }

    public function load(ObjectManager $manager): void
    {
        // 获取依赖的实体
        $classroom = $this->getReference(ClassroomFixtures::CLASSROOM_REFERENCE, Classroom::class);
        $course = $this->getReference(ClassroomFixtures::COURSE_REFERENCE, Course::class);

        // 获取或创建测试用户
        $student = $this->getOrCreateTestUser();

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

    private function getOrCreateTestUser(): UserInterface
    {
        // 尝试加载已存在的用户
        $user = $this->userManager->loadUserByIdentifier('train-classroom-student');

        // 如果用户不存在，创建一个新的测试用户
        if (null === $user) {
            $user = $this->userManager->createUser('train-classroom-student', '课堂培训学员');
            $this->userManager->saveUser($user);
        }

        return $user;
    }

    public function getDependencies(): array
    {
        return [
            ClassroomFixtures::class,
        ];
    }
}
