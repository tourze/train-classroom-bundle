<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command\DataLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;

/**
 * 数据库数据加载器
 */
class DatabaseDataLoader implements DataLoaderInterface
{
    public function supports(string $source): bool
    {
        return 'database' === $source;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function load(InputInterface $input, SymfonyStyle $io): array
    {
        $dsn = $input->getOption('database-dsn');
        if (!is_string($dsn)) {
            throw new InvalidArgumentException('使用数据库数据源时必须指定 --database-dsn 参数');
        }

        $io->info('从数据库加载数据: ' . $dsn);

        // 这里应该实现具体的数据库连接和查询逻辑
        // 为了简化，这里返回空数组
        $io->warning('数据库数据源功能尚未实现');

        return [];
    }
}
