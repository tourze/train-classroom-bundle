<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainClassroomBundle\Exception\RuntimeException;
use Tourze\TrainClassroomBundle\Enum\ClassroomStatus;
use Tourze\TrainClassroomBundle\Enum\ClassroomType;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;

/**
 * 教室管理服务实现
 *
 * 提供教室的创建、更新、查询、状态管理等核心业务功能
 */
class ClassroomService implements ClassroomServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ClassroomRepository $classroomRepository,
        private readonly ClassroomScheduleRepository $scheduleRepository,
        private readonly LoggerInterface $logger,
        private readonly Security $security,
    ) {
    }

    public function createClassroom(array $data): Classroom
    {
        $classroom = new Classroom();
        $this->populateClassroomData($classroom, $data);
        
        // 设置审计字段
        $user = $this->security->getUser();
        $classroom->setCreatedBy($user?->getUserIdentifier() ?? 'system');
        $classroom->setUpdatedBy($user?->getUserIdentifier() ?? 'system');
        
        $this->entityManager->persist($classroom);
        $this->entityManager->flush();
        
        $this->logger->info('教室创建成功', [
            'classroom_id' => $classroom->getId(),
            'name' => $classroom->getName(),
            'type' => $classroom->getType(),
            'capacity' => $classroom->getCapacity(),
        ]);
        
        return $classroom;
    }

    public function updateClassroom(Classroom $classroom, array $data): Classroom
    {
        $this->populateClassroomData($classroom, $data);
        
        // 更新审计字段
        $user = $this->security->getUser();
        $classroom->setUpdatedBy($user?->getUserIdentifier() ?? 'system');
        
        $this->entityManager->flush();
        
        $this->logger->info('教室信息更新成功', [
            'classroom_id' => $classroom->getId(),
            'name' => $classroom->getName(),
        ]);
        
        return $classroom;
    }

    public function deleteClassroom(Classroom $classroom): bool
    {
        try {
            // 检查是否有未完成的排课
            $activeSchedules = $this->scheduleRepository->findActiveSchedulesByClassroom($classroom);
            if (!empty($activeSchedules)) {
                throw new RuntimeException('教室存在未完成的排课，无法删除');
            }
            
            $this->entityManager->remove($classroom);
            $this->entityManager->flush();
            
            $this->logger->info('教室删除成功', [
                'classroom_id' => $classroom->getId(),
                'name' => $classroom->getName(),
            ]);
            
            return true;
        } catch (\Throwable $e) {
            $this->logger->error('教室删除失败', [
                'classroom_id' => $classroom->getId(),
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    public function getClassroomById(int $id): ?Classroom
    {
        return $this->classroomRepository->find($id);
    }

    public function getAvailableClassrooms(?ClassroomType $type = null, ?int $minCapacity = null, array $filters = []): array
    {
        $criteria = [
            'status' => ClassroomStatus::ACTIVE->value,
        ];
        
        if ($type !== null) {
            $criteria['type'] = $type;
        }
        
        $classrooms = $this->classroomRepository->findBy($criteria);
        
        // 过滤容量
        if ($minCapacity !== null) {
            $classrooms = array_filter($classrooms, function (Classroom $classroom) use ($minCapacity) {
                return $classroom->getCapacity() >= $minCapacity;
            });
        }
        
        // 应用其他过滤条件
        foreach ($filters as $field => $value) {
            $classrooms = array_filter($classrooms, function (Classroom $classroom) use ($field, $value) {
                switch ($field) {
                    case 'type':
                        return $classroom->getType() === $value;
                    case 'status':
                        return $classroom->getStatus() === $value;
                    case 'location':
                        return $classroom->getLocation() === $value;
                    case 'supplierId':
                        return $classroom->getSupplierId() === $value;
                    default:
                        return true;
                }
            });
        }
        
        return array_values($classrooms);
    }

    public function updateClassroomStatus(Classroom $classroom, ClassroomStatus $status, ?string $reason = null): Classroom
    {
        $oldStatus = $classroom->getStatus();
        $classroom->setStatus($status->value);
        
        // 更新审计字段
        $user = $this->security->getUser();
        $classroom->setUpdatedBy($user?->getUserIdentifier() ?? 'system');
        
        $this->entityManager->flush();
        
        $this->logger->info('教室状态更新', [
            'classroom_id' => $classroom->getId(),
            'old_status' => $oldStatus,
            'new_status' => $status->value,
            'reason' => $reason,
        ]);
        
        return $classroom;
    }

    public function isClassroomAvailable(Classroom $classroom, \DateTimeInterface $startTime, \DateTimeInterface $endTime): bool
    {
        // 检查教室状态
        if ($classroom->getStatus() !== ClassroomStatus::ACTIVE->value) {
            return false;
        }
        
        // 检查时间冲突
        $conflictingSchedules = $this->scheduleRepository->findConflictingSchedules(
            $classroom,
            \DateTimeImmutable::createFromInterface($startTime),
            \DateTimeImmutable::createFromInterface($endTime)
        );
        
        return empty($conflictingSchedules);
    }

    public function getClassroomUsageStats(Classroom $classroom, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $schedules = $this->scheduleRepository->findSchedulesByClassroomAndDateRange(
            $classroom,
            $startDate,
            $endDate
        );
        
        $totalHours = 0;
        $completedSessions = 0;
        $cancelledSessions = 0;
        $totalSessions = count($schedules);
        
        foreach ($schedules as $schedule) {
            $duration = $schedule->getEndTime()->diff($schedule->getStartTime());
            $totalHours += $duration->h + ($duration->i / 60);
            
            if ($schedule->getStatus() === ScheduleStatus::COMPLETED) {
                $completedSessions++;
            } elseif ($schedule->getStatus() === ScheduleStatus::CANCELLED) {
                $cancelledSessions++;
            }
        }
        
        // 计算可用时间（假设每天8小时工作时间）
        $daysDiff = $startDate->diff($endDate)->days + 1;
        $availableHours = $daysDiff * 8;
        $utilizationRate = $availableHours > 0 ? ($totalHours / $availableHours) * 100 : 0;
        
        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'cancelled_sessions' => $cancelledSessions,
            'total_hours' => round($totalHours, 2),
            'available_hours' => $availableHours,
            'utilization_rate' => round($utilizationRate, 2),
            'completion_rate' => $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 2) : 0,
        ];
    }

    public function getClassroomDevices(Classroom $classroom): array
    {
        return $classroom->getDevices() ?? [];
    }

    public function updateClassroomDevices(Classroom $classroom, array $devices): Classroom
    {
        $classroom->setDevices($devices);
        
        // 更新审计字段
        $user = $this->security->getUser();
        $classroom->setUpdatedBy($user?->getUserIdentifier() ?? 'system');
        
        $this->entityManager->flush();
        
        $this->logger->info('教室设备配置更新', [
            'classroom_id' => $classroom->getId(),
            'devices_count' => count($devices),
        ]);
        
        return $classroom;
    }

    public function getEnvironmentData(Classroom $classroom, ?\DateTimeInterface $startTime = null, ?\DateTimeInterface $endTime = null): array
    {
        // 这里应该集成环境监控系统的API
        // 目前返回模拟数据
        return [
            'temperature' => [
                'current' => 22.5,
                'min' => 20.0,
                'max' => 25.0,
                'unit' => '°C',
            ],
            'humidity' => [
                'current' => 45.0,
                'min' => 40.0,
                'max' => 60.0,
                'unit' => '%',
            ],
            'air_quality' => [
                'pm25' => 15,
                'co2' => 400,
                'status' => 'good',
            ],
            'lighting' => [
                'level' => 80,
                'unit' => '%',
            ],
            'noise_level' => [
                'current' => 35,
                'unit' => 'dB',
            ],
            'last_updated' => new \DateTime(),
        ];
    }

    public function batchImportClassrooms(array $classroomsData, bool $dryRun = false): array
    {
        $results = [
            'total' => count($classroomsData),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];
        
        foreach ($classroomsData as $index => $data) {
            try {
                // 验证必需字段
                if ((bool) empty($data['name'])) {
                    throw new InvalidArgumentException('教室名称不能为空');
                }
                
                // 检查是否已存在
                $existing = $this->classroomRepository->findOneBy(['name' => $data['name']]);
                if ($existing !== null) {
                    throw new InvalidArgumentException('教室名称已存在');
                }
                
                if (!$dryRun) {
                    $this->createClassroom($data);
                }
                
                $results['success']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'data' => $data,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        $this->logger->info('批量导入教室完成', $results);
        
        return $results;
    }

    /**
     * 填充教室数据
     */
    private function populateClassroomData(Classroom $classroom, array $data): void
    {
        if ((bool) isset($data['name'])) {
            $classroom->setTitle($data['name']);
        }
        
        if ((bool) isset($data['type'])) {
            $type = is_string($data['type']) ? ClassroomType::from($data['type']) : $data['type'];
            $classroom->setType($type->value);
        }
        
        if ((bool) isset($data['status'])) {
            $status = is_string($data['status']) ? ClassroomStatus::from($data['status']) : $data['status'];
            $classroom->setStatus($status->value);
        }
        
        if ((bool) isset($data['capacity'])) {
            $classroom->setCapacity((int) $data['capacity']);
        }
        
        if ((bool) isset($data['area'])) {
            $classroom->setArea((float) $data['area']);
        }
        
        if ((bool) isset($data['location'])) {
            $classroom->setLocation($data['location']);
        }
        
        if ((bool) isset($data['description'])) {
            $classroom->setDescription($data['description']);
        }
        
        if ((bool) isset($data['devices'])) {
            $classroom->setDevices($data['devices']);
        }
        
        if ((bool) isset($data['supplier_id'])) {
            $classroom->setSupplierId((int) $data['supplier_id']);
        }
    }
} 