<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;

/**
 * 教室使用统计计算器
 *
 * 负责计算教室使用率、完成率等统计数据
 */
class ClassroomUsageStatsCalculator
{
    /**
     * @param array<int, ClassroomSchedule> $schedules
     * @return array<string, mixed>
     */
    public function calculate(array $schedules, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $stats = $this->calculateScheduleStats($schedules);
        $availableHours = $this->calculateAvailableHours($startDate, $endDate);

        return $this->buildUsageStatsArray($stats, $availableHours);
    }

    /**
     * @param array<int, ClassroomSchedule> $schedules
     * @return array{totalHours: float, completedSessions: int, cancelledSessions: int, totalSessions: int}
     */
    private function calculateScheduleStats(array $schedules): array
    {
        $totalHours = 0.0;
        $completedSessions = 0;
        $cancelledSessions = 0;

        foreach ($schedules as $schedule) {
            $totalHours += $this->calculateScheduleHours($schedule);
            $completedSessions += $this->isScheduleCompleted($schedule) ? 1 : 0;
            $cancelledSessions += $this->isScheduleCancelled($schedule) ? 1 : 0;
        }

        return [
            'totalHours' => $totalHours,
            'completedSessions' => $completedSessions,
            'cancelledSessions' => $cancelledSessions,
            'totalSessions' => count($schedules),
        ];
    }

    private function calculateScheduleHours(ClassroomSchedule $schedule): float
    {
        $startTime = $schedule->getStartTime();
        $endTime = $schedule->getEndTime();

        $duration = $endTime->diff($startTime);

        return $duration->h + ($duration->i / 60);
    }

    private function isScheduleCompleted(ClassroomSchedule $schedule): bool
    {
        return ScheduleStatus::COMPLETED === $schedule->getScheduleStatus();
    }

    private function isScheduleCancelled(ClassroomSchedule $schedule): bool
    {
        return ScheduleStatus::CANCELLED === $schedule->getScheduleStatus();
    }

    private function calculateAvailableHours(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        $daysDiff = (false !== $startDate->diff($endDate)->days ? $startDate->diff($endDate)->days : 0) + 1;

        return $daysDiff * 8;
    }

    /**
     * @param array{totalHours: float, completedSessions: int, cancelledSessions: int, totalSessions: int} $stats
     * @return array<string, mixed>
     */
    private function buildUsageStatsArray(array $stats, int $availableHours): array
    {
        $utilizationRate = $this->calculateUtilizationRate($stats['totalHours'], $availableHours);
        $completionRate = $this->calculateCompletionRate($stats['completedSessions'], $stats['totalSessions']);

        return [
            'total_sessions' => $stats['totalSessions'],
            'completed_sessions' => $stats['completedSessions'],
            'cancelled_sessions' => $stats['cancelledSessions'],
            'total_hours' => round($stats['totalHours'], 2),
            'available_hours' => $availableHours,
            'utilization_rate' => round($utilizationRate, 2),
            'completion_rate' => $completionRate,
        ];
    }

    private function calculateUtilizationRate(float $totalHours, int $availableHours): float
    {
        if ($availableHours <= 0) {
            return 0.0;
        }

        return ($totalHours / $availableHours) * 100;
    }

    private function calculateCompletionRate(int $completedSessions, int $totalSessions): float
    {
        if ($totalSessions <= 0) {
            return 0.0;
        }

        return round(($completedSessions / $totalSessions) * 100, 2);
    }
}
