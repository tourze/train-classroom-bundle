<?php

namespace Tourze\TrainClassroomBundle\Controller;

use Carbon\Carbon;
use FileSystemBundle\Service\MountManager;
use SenboTrainingBundle\Entity\Registration;
use SenboTrainingBundle\Entity\Student;
use SenboTrainingBundle\Repository\QrcodeRepository;
use SenboTrainingBundle\Repository\RegistrationRepository;
use SenboTrainingBundle\Repository\StudentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\GAT2000\DocumentType;
use Tourze\IdcardManageBundle\Service\IdcardService;
use WeuiBundle\Service\NoticeService;

#[Route('/job-training/register')]
class RegisterController extends AbstractController
{
    public function __construct(
        private readonly QrcodeRepository $qrcodeRepository,
        private readonly IdcardService $idCardService,
        private readonly StudentRepository $studentRepository,
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
        if ($qrcode->getRegistrationCount() >= $qrcode->getLimitNumber()) {
            return $this->noticeService->weuiError('报名人数已满', '请联系平台重新提供报名二维码');
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
        if ($qrcode->getRegistrationCount() >= $qrcode->getLimitNumber()) {
            return $this->noticeService->weuiError('报名人数已满', '请联系平台重新提供报名二维码');
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

        // 以证件为唯一标志
        $student = $this->studentRepository->findOneBy(['idCardNumber' => $cardNumber]);
        if (!$student) {
            $student = new Student();
            $student->setIdCardType($cardType);
            $student->setIdCardNumber($cardNumber);
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
        if (!$student->getGender() && DocumentType::ID_CARD === $student->getIdCardType()) {
            $student->setGender($this->idCardService->getGender($student->getIdCardNumber()));
        }

        // TODO 检查手机号码
        $mobile = trim($request->request->get('mobile'));
        $captcha = trim($request->request->get('captcha'));
        $student->setPhoneNumber($mobile);

        // 名字
        if (empty($student->getRealName())) {
            $realName = $request->request->get('realName');
            $student->setRealName($realName);
        }

        // 白底照
        if (empty($student->getWhiteCertPhoto())) {
            $key = $mountManager->saveUploadFile($request->files->get('whiteCertPhoto'))->getFileKey();
            $student->setWhiteCertPhoto($mountManager->publicUrl($key));
        }
        // 证件人像照片
        if (empty($student->getIdCardPersonPhoto())) {
            $key = $mountManager->saveUploadFile($request->files->get('idPicture1'))->getFileKey();
            $student->setIdCardPersonPhoto($mountManager->publicUrl($key));
        }
        // 证件国徽照片
        if (empty($student->getIdCardFlagPhoto())) {
            $key = $mountManager->saveUploadFile($request->files->get('idPicture2'))->getFileKey();
            $student->setIdCardFlagPhoto($mountManager->publicUrl($key));
        }

        // 签名图片
        $signImage = $request->request->get('signImage');
        $student->setSignImage($signImage);

        // 保存
        $this->studentRepository->save($student);

        // 增加报名记录
        $registration = $this->registrationRepository->findOneBy([
            'student' => $student,
            'classroom' => $qrcode->getClassroom(),
        ]);
        if ($registration) {
            return $this->json([
                'code' => 1,
                'message' => '您已报名过，不需要重复报名',
            ]);
        }
        $registration = new Registration();
        $registration->setClassroom($qrcode->getClassroom());
        $registration->setStudent($student);
        $registration->setAge($student->getFaceAge());
        $registration->setQrcode($qrcode);
        $registration->setCourse($qrcode->getClassroom()->getCourse());
        $registration->setBank($qrcode->getClassroom()->getBank());
        $registration->setBeginTime(Carbon::now());
        $registration->setEndTime($qrcode->getClassroom()->getEndTime());
        $this->registrationRepository->save($registration);

        return $this->json([
            'code' => 1,
            'message' => 'success',
            'data' => [
                'id' => $student->getId(),
            ],
        ]);
    }
}
