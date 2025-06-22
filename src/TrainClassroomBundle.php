<?php

namespace Tourze\TrainClassroomBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;
use Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle;
use Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle;
use Tourze\IdcardManageBundle\IdcardManageBundle;
use Tourze\TrainClassroomBundle\Service\AttributeControllerLoader;

class TrainClassroomBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineIndexedBundle::class => ['all' => true],
            DoctrineSnowflakeBundle::class => ['all' => true],
            DoctrineTimestampBundle::class => ['all' => true],
            IdcardManageBundle::class => ['all' => true],
        ];
    }
    
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        AttributeControllerLoader::registerControllers($container, $this);
    }
}
