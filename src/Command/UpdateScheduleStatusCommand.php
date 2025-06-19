<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

/**
 * 排课状态更新命令
 * 
 * 自动更新过期或需要状态变更的排课记录
 */
#[AsCommand(
    name: self::NAME,
    description: '更新排课状态',
)]
class UpdateScheduleStatusCommand extends Command
{
    protected const NAME = 'train-classroom:update-schedule-status';
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, '试运行模式，不实际更新数据')
            ->addOption('batch-size', 'b', InputOption::VALUE_REQUIRED, '批处理大小', '100')
            ->addOption('force', 'f', InputOption::VALUE_NONE, '强制执行，忽略确认提示')
            ->setHelp('
此命令会自动更新排课状态：
1. 将已过期的"已排课"状态更新为"已完成"
2. 将开始时间已到的"已排课"状态更新为"进行中"
3. 将结束时间已过的"进行中"状态更新为"已完成"

使用示例：
  php bin/console train-classroom:update-schedule-status
  php bin/console train-classroom:update-schedule-status --dry-run
  php bin/console train-classroom:update-schedule-status --batch-size=50
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $batchSize = (int) $input->getOption('batch-size');
        $force = (bool) $input->getOption('force');

        $io->title('排课状态更新');

        if ((bool) $dryRun) {
            $io->note('运行在试运行模式，不会实际更新数据');
        }

        try {
            $now = new \DateTime();
            $stats = [
                'scheduled_to_in_progress' => 0,
                'in_progress_to_completed' => 0,
                'scheduled_to_completed' => 0,
                'total_processed' => 0,
            ];

            // 1. 更新已开始但状态仍为"已排课"的记录为"进行中"
            $io->section('更新已开始的排课为"进行中"状态');
            $scheduledToInProgress = $this->updateScheduledToInProgress($now, $batchSize, $dryRun, $io);
            $stats['scheduled_to_in_progress'] = $scheduledToInProgress;

            // 2. 更新已结束的"进行中"记录为"已完成"
            $io->section('更新已结束的排课为"已完成"状态');
            $inProgressToCompleted = $this->updateInProgressToCompleted($now, $batchSize, $dryRun, $io);
            $stats['in_progress_to_completed'] = $inProgressToCompleted;

            // 3. 更新已过期但仍为"已排课"的记录为"已完成"（跳过的课程）
            $io->section('更新已过期的排课为"已完成"状态');
            $scheduledToCompleted = $this->updateScheduledToCompleted($now, $batchSize, $dryRun, $io);
            $stats['scheduled_to_completed'] = $scheduledToCompleted;

            $stats['total_processed'] = $stats['scheduled_to_in_progress'] + 
                                      $stats['in_progress_to_completed'] + 
                                      $stats['scheduled_to_completed'];

            // 显示统计信息
            $io->section('更新统计');
            $io->table(
                ['状态变更', '数量'],
                [
                    ['已排课 → 进行中', $stats['scheduled_to_in_progress']],
                    ['进行中 → 已完成', $stats['in_progress_to_completed']],
                    ['已排课 → 已完成（过期）', $stats['scheduled_to_completed']],
                    ['总计', $stats['total_processed']],
                ]
            );

            if ($stats['total_processed'] > 0) {
                if ((bool) $dryRun) {
                    $io->success(sprintf('试运行完成，发现 %d 条记录需要更新', $stats['total_processed']));
                } else {
                    $io->success(sprintf('状态更新完成，共更新 %d 条记录', $stats['total_processed']));
                }
            } else {
                $io->info('没有需要更新的记录');
            }

            $this->logger->info('排课状态更新完成', [
                'dry_run' => $dryRun,
                'stats' => $stats,
            ]);

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $io->error('状态更新失败: ' . $e->getMessage());
            $this->logger->error('排课状态更新失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * 更新已开始的排课为"进行中"状态
     */
    private function updateScheduledToInProgress(\DateTime $now, int $batchSize, bool $dryRun, SymfonyStyle $io): int
    {
        $qb = $this->scheduleRepository->createQueryBuilder('s')
            ->where('s.status = :status')
            ->andWhere('s.startTime <= :now')
            ->andWhere('s.endTime > :now')
            ->setParameter('status', ScheduleStatus::SCHEDULED)
            ->setParameter('now', $now)
            ->setMaxResults($batchSize);

        $schedules = $qb->getQuery()->getResult();
        $count = count($schedules);

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($schedules as $schedule) {
                if (!$dryRun) {
                    $schedule->setStatus(ScheduleStatus::ONGOING);
                    $schedule->setUpdatedAt(new \DateTime());
                    $this->entityManager->persist($schedule);
                }
                $io->progressAdvance();
            }

            if (!$dryRun) {
                $this->entityManager->flush();
            }

            $io->progressFinish();
            $io->writeln(sprintf('处理了 %d 条记录', $count));
        } else {
            $io->writeln('没有需要更新的记录');
        }

        return $count;
    }

    /**
     * 更新已结束的"进行中"记录为"已完成"
     */
    private function updateInProgressToCompleted(\DateTime $now, int $batchSize, bool $dryRun, SymfonyStyle $io): int
    {
        $qb = $this->scheduleRepository->createQueryBuilder('s')
            ->where('s.status = :status')
            ->andWhere('s.endTime <= :now')
            ->setParameter('status', ScheduleStatus::ONGOING)
            ->setParameter('now', $now)
            ->setMaxResults($batchSize);

        $schedules = $qb->getQuery()->getResult();
        $count = count($schedules);

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($schedules as $schedule) {
                if (!$dryRun) {
                    $schedule->setStatus(ScheduleStatus::COMPLETED);
                    $schedule->setUpdatedAt(new \DateTime());
                    $this->entityManager->persist($schedule);
                }
                $io->progressAdvance();
            }

            if (!$dryRun) {
                $this->entityManager->flush();
            }

            $io->progressFinish();
            $io->writeln(sprintf('处理了 %d 条记录', $count));
        } else {
            $io->writeln('没有需要更新的记录');
        }

        return $count;
    }

    /**
     * 更新已过期但仍为"已排课"的记录为"已完成"
     */
    private function updateScheduledToCompleted(\DateTime $now, int $batchSize, bool $dryRun, SymfonyStyle $io): int
    {
        $qb = $this->scheduleRepository->createQueryBuilder('s')
            ->where('s.status = :status')
            ->andWhere('s.endTime <= :now')
            ->setParameter('status', ScheduleStatus::SCHEDULED)
            ->setParameter('now', $now)
            ->setMaxResults($batchSize);

        $schedules = $qb->getQuery()->getResult();
        $count = count($schedules);

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($schedules as $schedule) {
                if (!$dryRun) {
                    $schedule->setStatus(ScheduleStatus::COMPLETED);
                    $schedule->setUpdatedAt(new \DateTime());
                    $this->entityManager->persist($schedule);
                }
                $io->progressAdvance();
            }

            if (!$dryRun) {
                $this->entityManager->flush();
            }

            $io->progressFinish();
            $io->writeln(sprintf('处理了 %d 条记录（过期课程）', $count));
        } else {
            $io->writeln('没有需要更新的记录');
        }

        return $count;
    }
} 