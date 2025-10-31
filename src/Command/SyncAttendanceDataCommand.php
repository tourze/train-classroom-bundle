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
use Tourze\TrainClassroomBundle\Command\DataLoader\ApiDataLoader;
use Tourze\TrainClassroomBundle\Command\DataLoader\DatabaseDataLoader;
use Tourze\TrainClassroomBundle\Command\DataLoader\DataLoaderInterface;
use Tourze\TrainClassroomBundle\Command\DataLoader\FileDataLoader;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;

/**
 * 考勤数据同步命令
 *
 * 用于从外部设备同步考勤数据
 */
#[AsCommand(name: self::NAME, description: '同步考勤数据从外部设备或系统', help: <<<'TXT'

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
                
    TXT)]
class SyncAttendanceDataCommand extends Command
{
    protected const NAME = 'train-classroom:sync-attendance';

    /** @var array<DataLoaderInterface> */
    private readonly array $dataLoaders;

    public function __construct(
        private readonly AttendanceServiceInterface $attendanceService,
    ) {
        parent::__construct();
        $this->dataLoaders = [
            new FileDataLoader(),
            new ApiDataLoader(),
            new DatabaseDataLoader(),
        ];
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $input->getArgument('source');
        $dryRun = (bool) $input->getOption('dry-run');
        $batchSizeOption = $input->getOption('batch-size');
        $batchSize = is_numeric($batchSizeOption) ? (int) $batchSizeOption : 100;

        $io->title('考勤数据同步工具');

        if ($dryRun) {
            $io->note('运行在试运行模式，不会实际写入数据');
        }

        try {
            $sourceArg = is_string($source) ? $source : 'file';
            $attendanceData = $this->loadAttendanceData($sourceArg, $input, $io);

            if ([] === $attendanceData) {
                $io->warning('没有找到需要同步的考勤数据');

                return Command::SUCCESS;
            }

            $io->info(sprintf('找到 %d 条考勤记录', \count($attendanceData)));

            $results = $this->processBatchData($attendanceData, $batchSize, $dryRun, $io);

            return $this->handleSyncResults($results, $io);
        } catch (\Throwable $e) {
            $io->error('同步过程中发生错误: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @param array<int, array<string, mixed>> $attendanceData
     * @return array<string, int>
     */
    private function processBatchData(array $attendanceData, int $batchSize, bool $dryRun, SymfonyStyle $io): array
    {
        $batches = array_chunk($attendanceData, max(1, $batchSize));
        $totalSuccess = 0;
        $totalFailed = 0;

        $io->progressStart(\count($attendanceData));

        foreach ($batches as $batchIndex => $batch) {
            [$success, $failed] = $this->processSingleBatch($batch, $batchIndex, \count($batches), $dryRun, $io);
            $totalSuccess += $success;
            $totalFailed += $failed;
        }

        $io->progressFinish();

        return [
            'success' => $totalSuccess,
            'failed' => $totalFailed,
            'skipped' => 0,
            'total' => \count($attendanceData),
        ];
    }

    /**
     * 处理单个批次
     *
     * @param array<int, array<string, mixed>> $batch
     * @return array{int, int}
     */
    private function processSingleBatch(array $batch, int $batchIndex, int $totalBatches, bool $dryRun, SymfonyStyle $io): array
    {
        $io->section(sprintf('处理批次 %d/%d (%d 条记录)', $batchIndex + 1, $totalBatches, \count($batch)));

        $batchResults = $this->processBatch($batch, $dryRun, $io);
        $success = is_int($batchResults['success'] ?? 0) ? ($batchResults['success'] ?? 0) : 0;
        $failed = is_int($batchResults['failed'] ?? 0) ? ($batchResults['failed'] ?? 0) : 0;

        $io->progressAdvance(\count($batch));

        return [$success, $failed];
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     * @return array<string, mixed>
     */
    private function processBatch(array $batch, bool $dryRun, SymfonyStyle $io): array
    {
        if ($dryRun) {
            return $this->validateBatch($batch, $io);
        }

        return $this->importBatch($batch, $io);
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     * @return array<string, mixed>
     */
    private function validateBatch(array $batch, SymfonyStyle $io): array
    {
        $counts = ['success' => 0, 'failed' => 0];

        foreach ($batch as $index => $record) {
            $isValid = $this->validateAttendanceRecord($record);
            ++$counts[$isValid ? 'success' : 'failed'];

            if (!$isValid) {
                $io->error(sprintf('第 %d 行数据格式错误', $index + 1));
            }
        }

        return $counts;
    }

    /**
     * @param array<int, array<string, mixed>> $batch
     * @return array<string, mixed>
     */
    private function importBatch(array $batch, SymfonyStyle $io): array
    {
        $results = $this->attendanceService->batchImportAttendance($batch);

        if (isset($results['errors']) && is_array($results['errors']) && [] !== $results['errors']) {
            /** @var array<int, array<string, mixed>> $errors */
            $errors = $results['errors'];
            $this->showBatchErrors($errors, $io);
        }

        // Ensure proper return type structure
        return [
            'success' => is_int($results['success'] ?? 0) ? $results['success'] : 0,
            'failed' => is_int($results['failed'] ?? 0) ? $results['failed'] : 0,
            'errors' => $results['errors'] ?? [],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $errors
     */
    private function showBatchErrors(array $errors, SymfonyStyle $io): void
    {
        foreach ($errors as $error) {
            if (is_array($error) && isset($error['index'], $error['error'])) {
                $index = is_numeric($error['index']) ? (int) $error['index'] : 0;
                $errorMsg = is_string($error['error']) ? $error['error'] : '未知错误';
                $io->error(sprintf('第 %d 行错误: %s', $index + 1, $errorMsg));
            }
        }
    }

    /**
     * @param array<string, int> $results
     */
    private function handleSyncResults(array $results, SymfonyStyle $io): int
    {
        $io->success('数据同步完成');
        $io->table(
            ['统计项', '数量'],
            [
                ['成功', $results['success']],
                ['失败', $results['failed']],
                ['跳过', $results['skipped']],
                ['总计', $results['total']],
            ]
        );

        if ($results['failed'] > 0) {
            $io->warning(sprintf('有 %d 条记录同步失败，请检查错误信息', $results['failed']));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * 根据数据源类型加载考勤数据
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadAttendanceData(string $source, InputInterface $input, SymfonyStyle $io): array
    {
        foreach ($this->dataLoaders as $loader) {
            if ($loader->supports($source)) {
                return $loader->load($input, $io);
            }
        }

        throw new InvalidArgumentException('不支持的数据源类型: ' . $source);
    }

    /**
     * 验证考勤记录格式
     *
     * @param array<string, mixed> $record
     */
    private function validateAttendanceRecord(array $record): bool
    {
        return $this->hasRequiredFields($record)
            && $this->isValidAttendanceType($record)
            && $this->isValidAttendanceMethod($record);
    }

    /**
     * 检查必填字段
     *
     * @param array<string, mixed> $record
     */
    private function hasRequiredFields(array $record): bool
    {
        foreach (['registration_id', 'type', 'method'] as $field) {
            if (!isset($record[$field])) {
                return false;
            }

            if (!$this->isNonEmptyScalar($record[$field])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查是否为非空标量值
     *
     * @param mixed $value
     */
    private function isNonEmptyScalar($value): bool
    {
        return (is_string($value) || is_numeric($value)) && '' !== (string) $value;
    }

    /**
     * 验证考勤类型
     *
     * @param array<string, mixed> $record
     */
    private function isValidAttendanceType(array $record): bool
    {
        $validTypes = ['sign_in', 'sign_out', 'break_out', 'break_return'];

        return isset($record['type']) && \in_array($record['type'], $validTypes, true);
    }

    /**
     * 验证考勤方式
     *
     * @param array<string, mixed> $record
     */
    private function isValidAttendanceMethod(array $record): bool
    {
        $validMethods = ['face', 'card', 'fingerprint', 'qr_code', 'manual', 'mobile'];

        return isset($record['method']) && \in_array($record['method'], $validMethods, true);
    }
}
