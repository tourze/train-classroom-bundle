<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CatalogBundle\Entity\CatalogType;

#[When(env: 'test')]
#[When(env: 'dev')]
class TrainingCatalogTypeFixtures extends Fixture implements FixtureGroupInterface
{
    public const REFERENCE_TRAINING_TYPE = 'catalog-type-training';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function load(ObjectManager $manager): void
    {
        $trainingType = new CatalogType();
        $trainingType->setCode('training-' . uniqid());
        $trainingType->setName('培训分类');
        $trainingType->setDescription('用于培训系统的分类管理');
        $trainingType->setEnabled(true);
        $manager->persist($trainingType);

        $this->addReference(self::REFERENCE_TRAINING_TYPE, $trainingType);

        $manager->flush();
    }
}
