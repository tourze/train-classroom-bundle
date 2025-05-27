<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * TrainClassroom Bundle DI扩展
 */
class TrainClassroomExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        // 设置配置参数
        $container->setParameter('train_classroom.config', $config);
    }

    public function getAlias(): string
    {
        return 'train_classroom';
    }
}
