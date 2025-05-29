<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\ClassroomStatus;
use Tourze\TrainClassroomBundle\Enum\ClassroomType;
use Tourze\TrainClassroomBundle\Service\ClassroomServiceInterface;
use Tourze\TrainClassroomBundle\Service\DeviceServiceInterface;

/**
 * 教室管理API控制器
 * 
 * 提供教室的创建、更新、查询、状态管理等RESTful API接口
 */
#[Route('/api/classrooms', name: 'api_classroom_')]
class ClassroomController extends AbstractController
{
    public function __construct(
        private readonly ClassroomServiceInterface $classroomService,
        private readonly DeviceServiceInterface $deviceService,
    ) {
    }

    /**
     * 创建教室
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => '无效的JSON数据'], 400);
            }
            
            $classroom = $this->classroomService->createClassroom($data);
            
            return $this->json([
                'success' => true,
                'data' => $this->serializeClassroom($classroom),
                'message' => '教室创建成功',
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取教室详情
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $classroom = $this->classroomService->getClassroomById($id);
        
        if (!$classroom) {
            return $this->json(['error' => '教室不存在'], 404);
        }
        
        return $this->json([
            'success' => true,
            'data' => $this->serializeClassroom($classroom),
        ]);
    }

    /**
     * 更新教室信息
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => '无效的JSON数据'], 400);
            }
            
            $classroom = $this->classroomService->updateClassroom($classroom, $data);
            
            return $this->json([
                'success' => true,
                'data' => $this->serializeClassroom($classroom),
                'message' => '教室信息更新成功',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 删除教室
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $success = $this->classroomService->deleteClassroom($classroom);
            
            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => '教室删除成功',
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'error' => '教室删除失败',
                ], 400);
            }
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取可用教室列表
     */
    #[Route('/available', name: 'available', methods: ['GET'])]
    public function getAvailable(Request $request): JsonResponse
    {
        $type = $request->query->get('type');
        $minCapacity = $request->query->getInt('min_capacity');
        $filters = [];
        
        // 解析类型参数
        $classroomType = null;
        if ($type && ClassroomType::tryFrom($type)) {
            $classroomType = ClassroomType::from($type);
        }
        
        // 解析其他过滤条件
        if ($request->query->has('location')) {
            $filters['location'] = $request->query->get('location');
        }
        
        if ($request->query->has('supplier_id')) {
            $filters['supplier_id'] = $request->query->getInt('supplier_id');
        }
        
        $classrooms = $this->classroomService->getAvailableClassrooms(
            $classroomType,
            $minCapacity ?: null,
            $filters
        );
        
        return $this->json([
            'success' => true,
            'data' => array_map([$this, 'serializeClassroom'], $classrooms),
            'total' => count($classrooms),
        ]);
    }

    /**
     * 更新教室状态
     */
    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function updateStatus(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            $statusValue = $data['status'] ?? null;
            $reason = $data['reason'] ?? null;
            
            if (!$statusValue || !ClassroomStatus::tryFrom($statusValue)) {
                return $this->json(['error' => '无效的状态值'], 400);
            }
            
            $status = ClassroomStatus::from($statusValue);
            $classroom = $this->classroomService->updateClassroomStatus($classroom, $status, $reason);
            
            return $this->json([
                'success' => true,
                'data' => $this->serializeClassroom($classroom),
                'message' => '教室状态更新成功',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 检查教室可用性
     */
    #[Route('/{id}/availability', name: 'check_availability', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function checkAvailability(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            $startTime = new \DateTime($data['start_time'] ?? '');
            $endTime = new \DateTime($data['end_time'] ?? '');
            
            $available = $this->classroomService->isClassroomAvailable($classroom, $startTime, $endTime);
            
            return $this->json([
                'success' => true,
                'data' => [
                    'available' => $available,
                    'start_time' => $startTime->format('Y-m-d H:i:s'),
                    'end_time' => $endTime->format('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取教室使用统计
     */
    #[Route('/{id}/usage-stats', name: 'usage_stats', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getUsageStats(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $startDate = new \DateTime($request->query->get('start_date', '-30 days'));
            $endDate = new \DateTime($request->query->get('end_date', 'now'));
            
            $stats = $this->classroomService->getClassroomUsageStats($classroom, $startDate, $endDate);
            
            return $this->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取教室设备列表
     */
    #[Route('/{id}/devices', name: 'devices', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getDevices(int $id): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $devices = $this->deviceService->getClassroomDevices($classroom);
            
            return $this->json([
                'success' => true,
                'data' => $devices,
                'total' => count($devices),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 添加设备到教室
     */
    #[Route('/{id}/devices', name: 'add_device', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function addDevice(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return $this->json(['error' => '无效的JSON数据'], 400);
            }
            
            $device = $this->deviceService->addDevice($classroom, $data);
            
            return $this->json([
                'success' => true,
                'data' => $device,
                'message' => '设备添加成功',
            ], 201);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 获取教室环境数据
     */
    #[Route('/{id}/environment', name: 'environment', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getEnvironment(int $id, Request $request): JsonResponse
    {
        try {
            $classroom = $this->classroomService->getClassroomById($id);
            
            if (!$classroom) {
                return $this->json(['error' => '教室不存在'], 404);
            }
            
            $sensors = $request->query->all('sensors') ?? [];
            $data = $this->deviceService->getEnvironmentData($classroom, $sensors);
            
            return $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 批量导入教室
     */
    #[Route('/batch-import', name: 'batch_import', methods: ['POST'])]
    public function batchImport(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data || !isset($data['classrooms'])) {
                return $this->json(['error' => '无效的JSON数据'], 400);
            }
            
            $dryRun = $data['dry_run'] ?? false;
            $results = $this->classroomService->batchImportClassrooms($data['classrooms'], $dryRun);
            
            return $this->json([
                'success' => true,
                'data' => $results,
                'message' => $dryRun ? '批量导入预览完成' : '批量导入完成',
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * 序列化教室数据
     */
    private function serializeClassroom(Classroom $classroom): array
    {
        return [
            'id' => $classroom->getId(),
            'name' => $classroom->getName(),
            'type' => $classroom->getType()?->value,
            'status' => $classroom->getStatus()?->value,
            'capacity' => $classroom->getCapacity(),
            'area' => $classroom->getArea(),
            'location' => $classroom->getLocation(),
            'description' => $classroom->getDescription(),
            'devices' => $classroom->getDevices(),
            'supplier_id' => $classroom->getSupplierId(),
            'created_at' => $classroom->getCreateTime()?->format('Y-m-d H:i:s'),
            'updated_at' => $classroom->getUpdateTime()?->format('Y-m-d H:i:s'),
            'created_by' => $classroom->getCreatedBy(),
            'updated_by' => $classroom->getUpdatedBy(),
        ];
    }
}
