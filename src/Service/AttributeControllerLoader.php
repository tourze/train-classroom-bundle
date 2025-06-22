<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AttributeControllerLoader
{
    /**
     * 注册控制器服务
     */
    public static function registerControllers(ContainerBuilder $container, Bundle $bundle): void
    {
        $bundleDir = $bundle->getPath();
        $bundleNamespace = $bundle->getNamespace();
        
        // 扫描控制器目录
        $controllerDir = $bundleDir . '/Controller';
        if (!is_dir($controllerDir)) {
            return;
        }

        $controllerFiles = glob($controllerDir . '/**/*Controller.php');
        if (empty($controllerFiles)) {
            return;
        }

        foreach ($controllerFiles as $file) {
            $relativePath = str_replace($bundleDir . '/', '', $file);
            $relativePath = str_replace('/', '\\', $relativePath);
            $relativePath = str_replace('.php', '', $relativePath);
            
            $controllerClass = $bundleNamespace . '\\' . $relativePath;
            
            if (!class_exists($controllerClass)) {
                continue;
            }
            
            $reflection = new \ReflectionClass($controllerClass);
            if ($reflection->isAbstract() || !$reflection->isSubclassOf(AbstractController::class)) {
                continue;
            }

            // 创建控制器定义
            $definition = new Definition($controllerClass);
            $definition->setAutowired(true);
            $definition->setAutoconfigured(true);
            $definition->addTag('controller.service_arguments');
            
            $serviceId = 'train_classroom.controller.' . $reflection->getShortName();
            $container->setDefinition($serviceId, $definition);
        }
    }
}