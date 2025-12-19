<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command\DataLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;

/**
 * 文件数据加载器
 */
final class FileDataLoader implements DataLoaderInterface
{
    public function supports(string $source): bool
    {
        return 'file' === $source;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function load(InputInterface $input, SymfonyStyle $io): array
    {
        $filePath = $this->validateFilePath($input);
        $io->info('从文件加载数据: ' . $filePath);

        return $this->loadFileByExtension($filePath);
    }

    private function validateFilePath(InputInterface $input): string
    {
        $filePath = $input->getOption('file');

        if (!is_string($filePath)) {
            throw new InvalidArgumentException('使用文件数据源时必须指定 --file 参数');
        }

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('文件不存在: ' . $filePath);
        }

        return $filePath;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFileByExtension(string $filePath): array
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'csv' => $this->loadFromCsv($filePath),
            'json' => $this->loadFromJson($filePath),
            default => throw new InvalidArgumentException('不支持的文件格式: ' . $extension),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFromCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (false === $handle) {
            throw new RuntimeException('无法打开文件: ' . $filePath);
        }

        $headers = fgetcsv($handle);
        if (false === $headers) {
            fclose($handle);
            throw new RuntimeException('无法读取CSV表头');
        }

        $data = $this->parseCsvData($handle, array_map('strval', $headers));
        fclose($handle);

        return $data;
    }

    /**
     * @param resource $handle
     * @param array<int, string> $headers
     * @return array<int, array<string, mixed>>
     */
    private function parseCsvData($handle, array $headers): array
    {
        $data = [];
        $headerCount = \count($headers);

        while (($row = fgetcsv($handle)) !== false) {
            if (\count($row) === $headerCount) {
                $data[] = array_combine($headers, array_map('strval', $row));
            }
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadFromJson(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new RuntimeException('无法读取文件: ' . $filePath);
        }

        /** @var mixed $data */
        $data = json_decode($content, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('JSON格式错误: ' . json_last_error_msg());
        }

        return is_array($data) ? $this->filterValidArrayItems($data) : [];
    }

    /**
     * @param array<mixed> $items
     * @return array<int, array<string, mixed>>
     */
    private function filterValidArrayItems(array $items): array
    {
        /** @var array<int, array<string, mixed>> $validResult */
        $validResult = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                /** @var array<string, mixed> $item */
                $validResult[] = $item;
            }
        }

        return $validResult;
    }
}
