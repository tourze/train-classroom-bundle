<?php

namespace Tourze\TrainClassroomBundle\Controller;

use League\Flysystem\MountManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\GAT2000\DocumentType;
use Tourze\IdcardManageBundle\Service\IdcardService;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use WeuiBundle\Service\NoticeService;

#[Route('/job-training/register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly QrcodeRepository $qrcodeRepository,
        private readonly IdcardService $idCardService,
        private readonly RegistrationRepository $registrationRepository,
        private readonly NoticeService $noticeService,
    ) {
    }

    #[Route('/form/{id}', name: 'job-training-register-form')]
    public function genChance(string $id): Response
    {
        $qrcode = $this->qrcodeRepository->findOneBy([
            'id' => $id,
            'valid' => true,
        ]);
        if (!$qrcode) {
            throw new NotFoundHttpException('二维码无效');
        }
        // TODO: 实现报名人数检查逻辑
        // if ($qrcode->getRegistrationCount() >= $qrcode->getLimitNumber()) {
        //     return $this->noticeService->weuiError('报名人数已满', '请联系平台重新提供报名二维码');
        // }

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

    #[Route('/submit/{id}', name: 'job-training-register-submit-data')]
    public function submitData(string $id, Request $request, MountManager $mountManager): Response
    {
        $qrcode = $this->qrcodeRepository->findOneBy([
            'id' => $id,
            'valid' => true,
        ]);
        if (!$qrcode) {
            throw new NotFoundHttpException('二维码无效');
        }
        // TODO: 实现报名人数检查逻辑
        // if ($qrcode->getRegistrationCount() >= $qrcode->getLimitNumber()) {
        //     return $this->noticeService->weuiError('报名人数已满', '请联系平台重新提供报名二维码');
        // }

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
        $student = null; // 临时注释，需要根据实际情况实现
        if (!$student) {
            // $student = new Student();
            // $student->setIdCardType($cardType);
            // $student->setIdCardNumber($cardNumber);
            // TODO: 创建用户逻辑
        } else {
            $registration = $this->registrationRepository->findOneBy([
                'student' => $student,
                'classroom' => $qrcode->getClassroom(),
            ]);
            if ($registration) {
                return $this->json([
                    'code' => 1,
                    'message' => '您已经报名，不需要重复报名',
                ]);
            }
        }
        // TODO: 这里需要获取实际的学员实例
        // 增加报名记录
        // $registration = $this->registrationRepository->findOneBy([
        //     'student' => $student,
        //     'classroom' => $qrcode->getClassroom(),
        // ]);
        // if ($registration) {
        //     return $this->json([
        //         'code' => 1,
        //         'message' => '您已报名过，不需要重复报名',
        //     ]);
        // }
        // $registration = new Registration();
        // $registration->setClassroom($qrcode->getClassroom());
        // $registration->setStudent($student);
        // $registration->setAge($student->getFaceAge());
        // $registration->setQrcode($qrcode);
        // $registration->setCourse($qrcode->getClassroom()->getCourse());
        // $registration->setBank($qrcode->getClassroom()->getBank());
        // $registration->setBeginTime(Carbon::now());
        // $registration->setEndTime($qrcode->getClassroom()->getEndTime());
        // $this->registrationRepository->save($registration);

        // TODO 检查手机号码
        $mobile = trim($request->request->get('mobile'));
        $captcha = trim($request->request->get('captcha'));
        // $student->setPhoneNumber($mobile);

        // 名字
        // if (empty($student->getRealName())) {
        //     $realName = $request->request->get('realName');
        //     $student->setRealName($realName);
        // }

        // 文件上传逻辑也需要实现
        // 白底照
        // if (empty($student->getWhiteCertPhoto())) {
        //     $key = $mountManager->saveUploadFile($request->files->get('whiteCertPhoto'))->getFileKey();
        //     $student->setWhiteCertPhoto($mountManager->publicUrl($key));
        // }

        // 签名图片
        $signImage = $request->request->get('signImage');
        // $student->setSignImage($signImage);

        // 保存
        // $this->studentRepository->save($student);

        return $this->json([
            'code' => 0,
            'message' => '学员管理功能需要根据实际用户系统实现',
            'data' => [
                'id' => 'placeholder',
            ],
        ]);
    }
}
