<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;

#[When(env: 'test')]
#[When(env: 'dev')]
class TrainingCatalogFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    // 用于兼容旧的 CategoryFixtures 常量引用
    public const MAIN_RESPONSIBLE_PERSON_REFERENCE = 'main-responsible-person';
    public const SAFETY_MANAGEMENT_PERSONNEL_REFERENCE = 'safety-management-personnel';
    public const SPECIAL_OPERATION_PERSONNEL_REFERENCE = 'special-operation-personnel';
    public const BUILDING_CONSTRUCTION = 'training-building-construction';
    public const CHEMICAL_INDUSTRY = 'training-chemical-industry';
    public const MAIN_HAZARDOUS_CHEMICALS_REFERENCE = 'main-hazardous-chemicals';
    public const OTHER_INDUSTRY = 'training-other-industry';
    public const ELECTRICAL_WORK_REFERENCE = 'electrical-work';
    public const UNCATEGORIZED_REFERENCE = 'uncategorized';

    public static function getGroups(): array
    {
        return ['test', 'dev'];
    }

    public function getDependencies(): array
    {
        return [
            TrainingCatalogTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $trainingType = $this->getReference(TrainingCatalogTypeFixtures::REFERENCE_TRAINING_TYPE, CatalogType::class);

        $uniqueId = uniqid();

        // 创建一级分类（人员类型）
        $mainResponsiblePerson = $this->createCatalog($manager, $trainingType, 'main-responsible-person-' . $uniqueId, '主要负责人', 1000);
        $this->addReference(self::MAIN_RESPONSIBLE_PERSON_REFERENCE, $mainResponsiblePerson);

        $safetyManager = $this->createCatalog($manager, $trainingType, 'safety-manager-' . $uniqueId, '安全管理人员', 2000);
        $this->addReference(self::SAFETY_MANAGEMENT_PERSONNEL_REFERENCE, $safetyManager);

        $specialOperator = $this->createCatalog($manager, $trainingType, 'special-operator-' . $uniqueId, '特种作业人员', 3000);
        $this->addReference(self::SPECIAL_OPERATION_PERSONNEL_REFERENCE, $specialOperator);

        // 创建二级分类（行业类别）
        $buildingConstruction = $this->createCatalog($manager, $trainingType, 'building-construction-' . $uniqueId, '建筑施工', 1100, $mainResponsiblePerson);
        $this->addReference(self::BUILDING_CONSTRUCTION, $buildingConstruction);

        $chemicalIndustry = $this->createCatalog($manager, $trainingType, 'chemical-industry-' . $uniqueId, '化工行业', 1200, $mainResponsiblePerson);
        $this->addReference(self::CHEMICAL_INDUSTRY, $chemicalIndustry);
        $this->addReference(self::MAIN_HAZARDOUS_CHEMICALS_REFERENCE, $chemicalIndustry);

        $otherIndustry = $this->createCatalog($manager, $trainingType, 'other-industry-' . $uniqueId, '其他行业', 1300, $mainResponsiblePerson);
        $this->addReference(self::OTHER_INDUSTRY, $otherIndustry);

        // 添加额外的分类
        $electricalWork = $this->createCatalog($manager, $trainingType, 'electrical-work-' . $uniqueId, '电工作业', 3100, $specialOperator);
        $this->addReference(self::ELECTRICAL_WORK_REFERENCE, $electricalWork);

        $uncategorized = $this->createCatalog($manager, $trainingType, 'uncategorized-' . $uniqueId, '未分类', 9999);
        $this->addReference(self::UNCATEGORIZED_REFERENCE, $uncategorized);

        $manager->flush();
    }

    /**
     * 创建分类实体
     */
    private function createCatalog(
        ObjectManager $manager,
        CatalogType $type,
        string $slug,
        string $name,
        int $sortOrder,
        ?Catalog $parent = null,
    ): Catalog {
        $catalog = new Catalog();
        $catalog->setType($type);
        $catalog->setName($name);
        $catalog->setSortOrder($sortOrder);
        $catalog->setEnabled(true);

        if ($parent instanceof Catalog) {
            $catalog->setParent($parent);
        }

        $manager->persist($catalog);

        return $catalog;
    }
}
