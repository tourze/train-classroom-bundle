<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Api\Register;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\GAT2000\DocumentType;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;

final class RegisterFormController extends AbstractController
{
    public function __construct(
        private readonly QrcodeRepository $qrcodeRepository,
    ) {
    }

    #[Route('/job-training/register/form/{id}', name: 'job-training-register-form')]
    public function __invoke(string $id): Response
    {
        $qrcode = $this->qrcodeRepository->findOneBy([
            'id' => $id,
            'valid' => true,
        ]);
        if ($qrcode === null) {
            throw new NotFoundHttpException('二维码无效');
        }

        $cardTypes = [];
        foreach (DocumentType::cases() as $case) {
            $cardTypes[] = [
                'value' => $case->value,
                'label' => $case->getLabel(),
            ];
        }

        return $this->render('@JobTraining/register/form.html.twig', [
            'qrcode' => $qrcode,
            'classroom' => $qrcode->getClassroom(),
            'cardTypes' => $cardTypes,
        ]);
    }
}