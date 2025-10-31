<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

class AttendanceRecordFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // 获取依赖的Registration实体
        $registration = $this->getReference(RegistrationFixtures::REGISTRATION_REFERENCE, Registration::class);

        // 创建签到记录
        $checkInRecord = new AttendanceRecord();
        $checkInRecord->setRegistration($registration);
        $checkInRecord->setAttendanceType(AttendanceType::SIGN_IN);
        $checkInRecord->setAttendanceMethod(AttendanceMethod::QR_CODE);
        $checkInRecord->setAttendanceTime(new \DateTimeImmutable('-1 day 09:00'));
        $checkInRecord->setVerificationResult(VerificationResult::SUCCESS);
        $checkInRecord->setIsValid(true);

        $manager->persist($checkInRecord);

        // 创建签退记录
        $checkOutRecord = new AttendanceRecord();
        $checkOutRecord->setRegistration($registration);
        $checkOutRecord->setAttendanceType(AttendanceType::SIGN_OUT);
        $checkOutRecord->setAttendanceMethod(AttendanceMethod::FACE);
        $checkOutRecord->setAttendanceTime(new \DateTimeImmutable('-1 day 17:30'));
        $checkOutRecord->setVerificationResult(VerificationResult::SUCCESS);
        $checkOutRecord->setIsValid(true);
        $checkOutRecord->setDeviceId('DEVICE-001');
        $checkOutRecord->setDeviceLocation('教室A-101');

        $manager->persist($checkOutRecord);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RegistrationFixtures::class];
    }
}
