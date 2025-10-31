<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command\DataLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 数据加载器接口
 */
interface DataLoaderInterface
{
    /**
     * 加载考勤数据
     *
     * @return array<int, array<string, mixed>>
     */
    public function load(InputInterface $input, SymfonyStyle $io): array;

    /**
     * 判断是否支持该数据源
     */
    public function supports(string $source): bool;
}
