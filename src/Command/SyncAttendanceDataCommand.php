<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

/**
 * 考勤数据同步命令
 * 
 * 用于从外部设备同步考勤数据
 */
#[AsCommand(
    name: 'train-classroom:sync-attendance',
    description: '同步考勤数据从外部设备或系统'
)]
class SyncAttendanceDataCommand extends Command
{
    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::OPTIONAL, '数据源类型 (file|api|database)', 'file')
            ->addOption('file', 'f', InputOption::VALUE_REQUIRED, '数据文件路径')
            ->addOption('api-url', null, InputOption::VALUE_REQUIRED, 'API接口地址')
            ->addOption('database-dsn', null, InputOption::VALUE_REQUIRED, '数据库连接字符串')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际写入数据')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, '批处理大小', '100')
            ->addOption('date-from', null, InputOption::VALUE_REQUIRED, '同步开始日期 (Y-m-d)')
            ->addOption('date-to', null, InputOption::VALUE_REQUIRED, '同步结束日期 (Y-m-d)')
            ->setHelp('
此命令用于从外部考勤设备或系统同步考勤数据。

支持的数据源类型：
  file      - 从CSV或JSON文件导入
  api       - 从REST API接口获取
  database  - 从外部数据库同步

示例用法：
  # 从CSV文件导入
  php bin/console train-classroom:sync-attendance file --file=/path/to/attendance.csv
  
  # 从API接口同步
  php bin/console train-classroom:sync-attendance api --api-url=https://device.example.com/api/attendance
  
  # 试运行模式
  php bin/console train-classroom:sync-attendance file --file=/path/to/data.csv --dry-run
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $dryRun = (bool) $input->getOption('dry-run');
        $batchSize = (int) (int) $input->getOption('batch-size');

        $io->title('考勤数据同步工具');

        if ($dryRun) {
            $io->note('运行在试运行模式，不会实际写入数据');
        }

        try {
            $attendanceData = $this->loadAttendanceData($source, $input, $io);

            if (empty($attendanceData)) {
                $io->warning('没有找到需要同步的考勤数据');
                return Command::SUCCESS;
            }

            $io->info(sprintf('找到 %d 条考勤记录', count($attendanceData)));

            // 分批处理数据
            $batches = array_chunk($attendanceData, $batchSize);
            $totalBatches = count($batches);
            $totalSuccess = 0;
            $totalFailed = 0;
            $totalSkipped = 0;

            $io->progressStart(count($attendanceData));

            foreach ($batches as $batchIndex => $batch) {
                $io->section(sprintf('处理批次 %d/%d (%d 条记录)', $batchIndex + 1, $totalBatches, count($batch)));

                if (!$dryRun) {
                    $results = $this->attendanceService->batchImportAttendance($batch);
                    $totalSuccess += $results['success'];
                    $totalFailed += $results['failed'];

                    // 显示错误详情
                    if (!empty($results['errors'])) {
                        foreach ($results['errors'] as $error) {
                            $io->error(sprintf('第 %d 行错误: %s', $error['index'] + 1, $error['error']));
                        }
                    }
                } else {
                    // 试运行模式，只验证数据格式
                    foreach ($batch as $index => $record) {
                        if ($this->validateAttendanceRecord($record)) {
                            $totalSuccess++;
                        } else {
                            $totalFailed++;
                            $io->error(sprintf('第 %d 行数据格式错误', $index + 1));
                        }
                    }
                }

                $io->progressAdvance(count($batch));
            }

            $io->progressFinish();

            // 显示同步结果
            $io->success('数据同步完成');
            $io->table(
                ['统计项', '数量'],
                [
                    ['成功', $totalSuccess],
                    ['失败', $totalFailed],
                    ['跳过', $totalSkipped],
                    ['总计', count($attendanceData)],
                ]
            );

            if ($totalFailed > 0) {
                $io->warning(sprintf('有 %d 条记录同步失败，请检查错误信息', $totalFailed));
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('同步过程中发生错误: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 根据数据源类型加载考勤数据
     */
    private function loadAttendanceData(string $source, InputInterface $input, SymfonyStyle $io): array
    {
        switch ($source) {
            case 'file':
                return $this->loadFromFile($input, $io);
            case 'api':
                return $this->loadFromApi($input, $io);
            case 'database':
                return $this->loadFromDatabase($input, $io);
            default:
                throw new \InvalidArgumentException('不支持的数据源类型: ' . $source);
        }
    }

    /**
     * 从文件加载数据
     */
    private function loadFromFile(InputInterface $input, SymfonyStyle $io): array
    {
        $filePath = $input->getOption('file');
        if (($filePath === null)) {
            throw new \InvalidArgumentException('使用文件数据源时必须指定 --file 参数');
        }

        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException('文件不存在: ' . $filePath);
        }

        $io->info('从文件加载数据: ' . $filePath);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'csv':
                return $this->loadFromCsv($filePath);
            case 'json':
                return $this->loadFromJson($filePath);
            default:
                throw new \InvalidArgumentException('不支持的文件格式: ' . $extension);
        }
    }

    /**
     * 从CSV文件加载数据
     */
    private function loadFromCsv(string $filePath): array
    {
        $data = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('无法打开文件: ' . $filePath);
        }

        // 读取表头
        $headers = fgetcsv($handle);
        if ($headers === false) {
            throw new \RuntimeException('无法读取CSV表头');
        }

        // 读取数据行
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === count($headers)) {
                $data[] = array_combine($headers, $row);
            }
        }

        fclose($handle);
        return $data;
    }

    /**
     * 从JSON文件加载数据
     */
    private function loadFromJson(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \RuntimeException('无法读取文件: ' . $filePath);
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON格式错误: ' . json_last_error_msg());
        }

        return is_array($data) ? $data : [];
    }

    /**
     * 从API加载数据
     */
    private function loadFromApi(InputInterface $input, SymfonyStyle $io): array
    {
        $apiUrl = $input->getOption('api-url');
        if (($apiUrl === null)) {
            throw new \InvalidArgumentException('使用API数据源时必须指定 --api-url 参数');
        }

        $io->info('从API加载数据: ' . $apiUrl);

        // 构建查询参数
        $params = [];
        if ($dateFrom = $input->getOption('date-from')) {
            $params['date_from'] = $dateFrom;
        }
        if ($dateTo = $input->getOption('date-to')) {
            $params['date_to'] = $dateTo;
        }

        $url = $apiUrl . '?' . http_build_query($params);

        // 发送HTTP请求
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Accept: application/json',
                    'User-Agent: TrainClassroom-SyncCommand/1.0',
                ],
                'timeout' => 30,
            ],
        ]);

        $response = file_get_contents($url, false, $context);
        if ($response === false) {
            throw new \RuntimeException('API请求失败: ' . $url);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('API响应JSON格式错误: ' . json_last_error_msg());
        }

        return $data['data'] ?? $data;
    }

    /**
     * 从数据库加载数据
     */
    private function loadFromDatabase(InputInterface $input, SymfonyStyle $io): array
    {
        $dsn = $input->getOption('database-dsn');
        if (($dsn === null)) {
            throw new \InvalidArgumentException('使用数据库数据源时必须指定 --database-dsn 参数');
        }

        $io->info('从数据库加载数据: ' . $dsn);

        // 这里应该实现具体的数据库连接和查询逻辑
        // 为了简化，这里返回空数组
        $io->warning('数据库数据源功能尚未实现');
        return [];
    }

    /**
     * 验证考勤记录格式
     */
    private function validateAttendanceRecord(array $record): bool
    {
        $requiredFields = ['registration_id', 'type', 'method'];

        foreach ($requiredFields as $field) {
            if (!isset($record[$field]) || empty($record[$field])) {
                return false;
            }
        }

        // 验证枚举值
        if (!in_array($record['type'], ['sign_in', 'sign_out', 'break_out', 'break_return'])) {
            return false;
        }

        if (!in_array($record['method'], ['face', 'card', 'fingerprint', 'qr_code', 'manual', 'mobile'])) {
            return false;
        }

        return true;
    }
} 