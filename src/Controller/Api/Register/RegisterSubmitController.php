<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Register;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RegisterSubmitController extends AbstractController
{
    #[Route(path: '/api/register/submit', name: 'api-register-submit', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        try {
            // 检查用户是否已认证
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return $this->json([
                    'success' => false,
                    'message' => '无效的请求数据',
                ]);
            }

            $email = $data['email'] ?? null;
            $name = $data['name'] ?? null;
            $courseId = $data['course_id'] ?? null;

            // 验证必填参数
            if (null === $email) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少邮箱参数',
                ]);
            }

            if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json([
                    'success' => false,
                    'message' => '无效的邮箱格式',
                ]);
            }

            if (null === $name) {
                return $this->json([
                    'success' => false,
                    'message' => '缺少姓名参数',
                ]);
            }

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
            // 实际实现应该从课程仓库查找课程并创建报名记录
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
