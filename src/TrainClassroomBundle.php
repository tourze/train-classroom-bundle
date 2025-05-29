<?php

namespace Tourze\TrainClassroomBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;

class TrainClassroomBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle::class => ['all' => true],
            \Tourze\DoctrineSnowflakeBundle\DoctrineSnowflakeBundle::class => ['all' => true],
            \Tourze\DoctrineTimestampBundle\DoctrineTimestampBundle::class => ['all' => true],
        ];
    }
}
