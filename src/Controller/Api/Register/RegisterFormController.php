<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Register;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RegisterFormController extends AbstractController
{
    #[Route(path: '/api/register/form', name: 'api-register-form', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        try {
            // 检查用户是否已认证
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $courseId = $request->query->get('course_id');
            if (null === $courseId) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少课程ID参数',
                ]);
            }

            if (!is_numeric($courseId)) {
                return $this->json([
                    'success' => false,
                    'message' => '无效的课程ID',
                ]);
            }

            // 临时实现 - 模拟课程不存在的情况
            // 实际实现应该从课程仓库查找课程
            return $this->json([
                'success' => false,
                'message' => '课程不存在',
            ]);
        } catch (AccessDeniedException $e) {
            return $this->json([
                'success' => false,
                'message' => '访问被拒绝，请先登录',
            ], Response::HTTP_FORBIDDEN);
        }
    }
}
