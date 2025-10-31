<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command\DataLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;

/**
 * API数据加载器
 */
class ApiDataLoader implements DataLoaderInterface
{
    public function supports(string $source): bool
    {
        return 'api' === $source;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function load(InputInterface $input, SymfonyStyle $io): array
    {
        $apiUrl = $this->validateApiUrl($input);
        $io->info('从API加载数据: ' . $apiUrl);

        $url = $this->buildApiUrl($apiUrl, $input);
        $response = $this->fetchApiResponse($url);
        $data = $this->parseApiResponse($response);

        return $this->extractValidRecords($data);
    }

    private function validateApiUrl(InputInterface $input): string
    {
        $apiUrl = $input->getOption('api-url');

        if (!is_string($apiUrl)) {
            throw new InvalidArgumentException('使用API数据源时必须指定 --api-url 参数');
        }

        return $apiUrl;
    }

    private function buildApiUrl(string $baseUrl, InputInterface $input): string
    {
        $params = $this->buildApiQueryParams($input);
        $queryString = http_build_query($params);

        return ('' !== $queryString) ? $baseUrl . '?' . $queryString : $baseUrl;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildApiQueryParams(InputInterface $input): array
    {
        $params = [];

        $dateFrom = $input->getOption('date-from');
        if (null !== $dateFrom) {
            $params['date_from'] = $dateFrom;
        }

        $dateTo = $input->getOption('date-to');
        if (null !== $dateTo) {
            $params['date_to'] = $dateTo;
        }

        return $params;
    }

    private function fetchApiResponse(string $url): string
    {
        $context = $this->createHttpContext();
        $response = @file_get_contents($url, false, $context);

        if (false === $response) {
            throw new RuntimeException('API请求失败: ' . $url);
        }

        return $response;
    }

    /**
     * @return resource
     */
    private function createHttpContext()
    {
        return stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: application/json',
                    'User-Agent: TrainClassroom-SyncCommand/1.0',
                ],
                'timeout' => 30,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseApiResponse(string $response): array
    {
        /** @var mixed $data */
        $data = json_decode($response, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new RuntimeException('API响应JSON格式错误: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new RuntimeException('API响应数据格式错误：预期数组');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, mixed>>
     */
    private function extractValidRecords(array $data): array
    {
        $result = $this->extractDataArray($data);

        return $this->filterValidArrayItems($result);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<mixed>
     */
    private function extractDataArray(array $data): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        return $data;
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
