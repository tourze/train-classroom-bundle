<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;

/**
 * 数据清理命令
 * 
 * 用于清理过期的考勤记录、排课记录和相关文件
 */
#[AsCommand(
    name: 'train-classroom:cleanup-data',
    description: '清理过期的考勤记录和相关文件'
)]
class CleanupDataCommand extends Command
{
    protected const NAME = 'train-classroom:cleanup-data';
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParameterBagInterface $parameterBag
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('attendance-days', null, InputOption::VALUE_REQUIRED, '考勤记录保留天数', null)
            ->addOption('video-days', null, InputOption::VALUE_REQUIRED, '视频文件保留天数', null)
            ->addOption('schedule-days', null, InputOption::VALUE_REQUIRED, '已完成排课记录保留天数', '90')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际删除数据')
            ->addOption('force', null, InputOption::VALUE_NONE, '强制执行，跳过确认提示')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, '批处理大小', '1000')
            ->setHelp('
此命令用于清理过期的培训数据，包括：
- 考勤记录
- 视频文件
- 已完成的排课记录

清理规则基于Bundle配置或命令行参数。

示例用法：
  # 使用默认配置清理
  php bin/console train-classroom:cleanup-data
  
  # 自定义保留天数
  php bin/console train-classroom:cleanup-data --attendance-days=365 --video-days=180
  
  # 试运行模式
  php bin/console train-classroom:cleanup-data --dry-run
  
  # 强制执行
  php bin/console train-classroom:cleanup-data --force
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $force = (bool) $input->getOption('force');
        $batchSize = (int) (int) $input->getOption('batch-size');

        $io->title('数据清理工具');

        if ((bool) $dryRun) {
            $io->note('运行在试运行模式，不会实际删除数据');
        }

        try {
            // 获取配置
            $config = $this->getCleanupConfig($input);
            
            $io->section('清理配置');
            $io->table(
                ['项目', '保留天数'],
                [
                    ['考勤记录', $config['attendance_days']],
                    ['视频文件', $config['video_days']],
                    ['排课记录', $config['schedule_days']],
                ]
            );

            if (!$force && (bool) !$dryRun) {
                if (!$io->confirm('确认要执行数据清理吗？此操作不可逆！', false)) {
                    $io->info('操作已取消');
                    return Command::SUCCESS;
                }
            }

            $totalCleaned = 0;

            // 清理考勤记录
            $attendanceCleaned = $this->cleanupAttendanceRecords($config['attendance_days'], $batchSize, $dryRun, $io);
            $totalCleaned += $attendanceCleaned;

            // 清理排课记录
            $scheduleCleaned = $this->cleanupScheduleRecords($config['schedule_days'], $batchSize, $dryRun, $io);
            $totalCleaned += $scheduleCleaned;

            // 清理视频文件
            $videoCleaned = $this->cleanupVideoFiles($config['video_days'], $dryRun, $io);
            $totalCleaned += $videoCleaned;

            $io->success(sprintf('数据清理完成，共清理 %d 项数据', $totalCleaned));

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('清理过程中发生错误: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * 获取清理配置
     */
    private function getCleanupConfig(InputInterface $input): array
    {
        $bundleConfig = $this->parameterBag->get('train_classroom.config');
        $archiveConfig = $bundleConfig['archive'] ?? [];

        return [
            'attendance_days' => $input->getOption('attendance-days') 
                ?? $archiveConfig['attendance_retention_days'] 
                ?? 1095, // 默认3年
            'video_days' => $input->getOption('video-days') 
                ?? $archiveConfig['video_retention_days'] 
                ?? 365, // 默认1年
            'schedule_days' => (int) $input->getOption('schedule-days'),
        ];
    }

    /**
     * 清理考勤记录
     */
    private function cleanupAttendanceRecords(int $retentionDays, int $batchSize, bool $dryRun, SymfonyStyle $io): int
    {
        $io->section('清理考勤记录');

        $cutoffDate = new \DateTimeImmutable("-{$retentionDays} days");
        $io->info(sprintf('清理 %s 之前的考勤记录', $cutoffDate->format('Y-m-d')));

        $repository = $this->entityManager->getRepository(AttendanceRecord::class);
        
        // 统计需要清理的记录数
        $qb = $repository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.recordTime < :cutoffDate')
            ->setParameter('cutoffDate', $cutoffDate);

        $totalCount = (int) $qb->getQuery()->getSingleScalarResult();

        if ($totalCount === 0) {
            $io->info('没有需要清理的考勤记录');
            return 0;
        }

        $io->info(sprintf('找到 %d 条需要清理的考勤记录', $totalCount));

        if ((bool) $dryRun) {
            return $totalCount;
        }

        // 分批删除
        $deletedCount = 0;
        $io->progressStart($totalCount);

        while ($deletedCount < $totalCount) {
            $qb = $repository->createQueryBuilder('a')
                ->where('a.recordTime < :cutoffDate')
                ->setParameter('cutoffDate', $cutoffDate)
                ->setMaxResults($batchSize);

            $records = $qb->getQuery()->getResult();

            if ((bool) empty($records)) {
                break;
            }

            foreach ($records as $record) {
                $this->entityManager->remove($record);
                $deletedCount++;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $io->progressAdvance(count($records));
        }

        $io->progressFinish();
        $io->info(sprintf('已清理 %d 条考勤记录', $deletedCount));

        return $deletedCount;
    }

    /**
     * 清理排课记录
     */
    private function cleanupScheduleRecords(int $retentionDays, int $batchSize, bool $dryRun, SymfonyStyle $io): int
    {
        $io->section('清理排课记录');

        $cutoffDate = new \DateTimeImmutable("-{$retentionDays} days");
        $io->info(sprintf('清理 %s 之前已完成的排课记录', $cutoffDate->format('Y-m-d')));

        $repository = $this->entityManager->getRepository(ClassroomSchedule::class);
        
        // 统计需要清理的记录数（只清理已完成的排课）
        $qb = $repository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.endTime < :cutoffDate')
            ->andWhere('s.status = :completedStatus')
            ->setParameter('cutoffDate', $cutoffDate)
            ->setParameter('completedStatus', 'completed');

        $totalCount = (int) $qb->getQuery()->getSingleScalarResult();

        if ($totalCount === 0) {
            $io->info('没有需要清理的排课记录');
            return 0;
        }

        $io->info(sprintf('找到 %d 条需要清理的排课记录', $totalCount));

        if ((bool) $dryRun) {
            return $totalCount;
        }

        // 分批删除
        $deletedCount = 0;
        $io->progressStart($totalCount);

        while ($deletedCount < $totalCount) {
            $qb = $repository->createQueryBuilder('s')
                ->where('s.endTime < :cutoffDate')
                ->andWhere('s.status = :completedStatus')
                ->setParameter('cutoffDate', $cutoffDate)
                ->setParameter('completedStatus', 'completed')
                ->setMaxResults($batchSize);

            $records = $qb->getQuery()->getResult();

            if ((bool) empty($records)) {
                break;
            }

            foreach ($records as $record) {
                $this->entityManager->remove($record);
                $deletedCount++;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $io->progressAdvance(count($records));
        }

        $io->progressFinish();
        $io->info(sprintf('已清理 %d 条排课记录', $deletedCount));

        return $deletedCount;
    }

    /**
     * 清理视频文件
     */
    private function cleanupVideoFiles(int $retentionDays, bool $dryRun, SymfonyStyle $io): int
    {
        $io->section('清理视频文件');

        $cutoffDate = new \DateTimeImmutable("-{$retentionDays} days");
        $io->info(sprintf('清理 %s 之前的视频文件', $cutoffDate->format('Y-m-d')));

        // 这里应该实现具体的视频文件清理逻辑
        // 可能需要：
        // 1. 扫描视频存储目录
        // 2. 检查文件创建时间
        // 3. 删除过期文件
        // 4. 更新数据库中的文件记录

        $videoStoragePath = $this->parameterBag->get('kernel.project_dir') . '/var/videos';
        
        if (!is_dir($videoStoragePath)) {
            $io->info('视频存储目录不存在，跳过视频文件清理');
            return 0;
        }

        $deletedCount = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($videoStoragePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileTime = new \DateTimeImmutable('@' . $file->getMTime());
                
                if ($fileTime < $cutoffDate) {
                    $io->text(sprintf('发现过期视频文件: %s (创建于 %s)', 
                        $file->getPathname(), 
                        $fileTime->format('Y-m-d H:i:s')
                    ));

                    if (!$dryRun) {
                        if (unlink($file->getPathname())) {
                            $deletedCount++;
                        } else {
                            $io->warning(sprintf('无法删除文件: %s', $file->getPathname()));
                        }
                    } else {
                        $deletedCount++;
                    }
                }
            }
        }

        if ($deletedCount > 0) {
            $io->info(sprintf('已清理 %d 个视频文件', $deletedCount));
        } else {
            $io->info('没有需要清理的视频文件');
        }

        return $deletedCount;
    }
} 