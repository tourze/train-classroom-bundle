<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Register;

use League\Flysystem\MountManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\GAT2000\DocumentType;
use Tourze\IdcardManageBundle\Service\IdcardService;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;

final class RegisterSubmitController extends AbstractController
{
    public function __construct(
        private readonly QrcodeRepository $qrcodeRepository,
        private readonly IdcardService $idCardService,
    ) {
    }

    #[Route(path: '/job-training/register/submit/{id}', name: 'job-training-register-submit-data')]
    public function __invoke(string $id, Request $request, MountManager $mountManager): Response
    {
        $qrcode = $this->qrcodeRepository->findOneBy([
            'id' => $id,
            'valid' => true,
        ]);
        if ($qrcode === null) {
            throw new NotFoundHttpException('二维码无效');
        }

        // 检查下证件
        $cardNumber = trim($request->request->get('cardNumber'));
        $cardType = DocumentType::from($request->request->get('cardType'));
        if (DocumentType::ID_CARD === $cardType) {
            // 身份证的话，我们先做一次简单的校验
            if (!$this->idCardService->isValid($cardNumber)) {
                return $this->json([
                    'code' => -1,
                    'message' => '身份证不合法，请检查',
                ]);
            }
        }

        // TODO: 这里需要根据实际的用户系统来查找用户
        // $student = $this->studentRepository->findOneBy(['idCardNumber' => $cardNumber]);
        // 临时返回待实现信息
        return $this->json([
            'code' => -2,
            'message' => '学员管理功能需要根据实际用户系统实现',
            'data' => [
                'card_number' => $cardNumber,
                'card_type' => $cardType->value,
            ],
        ]);
    }
}