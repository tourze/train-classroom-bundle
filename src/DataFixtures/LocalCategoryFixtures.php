<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class LocalCategoryFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 为了兼容性，直接引用 TrainingCatalogFixtures 的常量
    public const MAIN_RESPONSIBLE_PERSON_REFERENCE = TrainingCatalogFixtures::MAIN_RESPONSIBLE_PERSON_REFERENCE;

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function getDependencies(): array
    {
        return [
            TrainingCatalogFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // 这个 fixture 现在只是为了兼容性而存在
        // 实际的数据已经在 TrainingCatalogFixtures 中创建
        $manager->flush();
    }
}
