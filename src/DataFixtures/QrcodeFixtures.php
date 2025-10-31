<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Qrcode;

class QrcodeFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $classroom = $this->getReference(ClassroomFixtures::CLASSROOM_REFERENCE, Classroom::class);

        // 为测试教室创建3个二维码
        for ($j = 1; $j <= 3; ++$j) {
            $qrcode = new Qrcode();

            $qrcode->setClassroom($classroom);
            /** @var string $titlePrefix */
            $titlePrefix = $this->faker->randomElement([
                '入学报名码',
                '补录二维码',
                '特殊报名通道',
                'VIP报名码',
            ]);
            $qrcode->setTitle($titlePrefix . ' ' . $j);

            $qrcode->setLimitNumber($this->faker->numberBetween(10, 50));
            $qrcode->setValid($this->faker->boolean(80)); // 80%概率为有效

            $manager->persist($qrcode);
            $this->addReference('qrcode_test_' . $j, $qrcode);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ClassroomFixtures::class];
    }
}
