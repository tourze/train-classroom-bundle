<?php

namespace Tourze\TrainClassroomBundle\Procedure;

use SenboTrainingBundle\Repository\RegistrationRepository;
use SenboTrainingBundle\Repository\StudentRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;

#[MethodDoc('获取当前学员的班级信息')]
#[MethodExpose('GetJobTrainingJoinedClassroomList')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class GetJobTrainingJoinedClassroomList extends BaseProcedure
{
    public function __construct(
        private readonly StudentRepository $studentRepository,
        private readonly Security $security,
        private readonly RegistrationRepository $registrationRepository,
    ) {
    }

    public function execute(): array
    {
        $student = $this->studentRepository->findStudent($this->security->getUser());
        if (!$student) {
            throw new ApiException('请先绑定学员信息', -885);
        }

        $registrations = $this->registrationRepository->findBy(['student' => $student]);
        $list = [];
        foreach ($registrations as $registration) {
            $list[] = $registration->retrieveApiArray();
        }

        return [
            'list' => $list,
        ];
    }
}
