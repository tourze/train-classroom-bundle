<?php

namespace Tourze\TrainClassroomBundle\Procedure;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

#[MethodDoc(summary: '获取当前学员的班级信息')]
#[MethodExpose(method: 'GetJobTrainingJoinedClassroomList')]
#[IsGranted(attribute: 'IS_AUTHENTICATED_FULLY')]
class GetJobTrainingJoinedClassroomList extends BaseProcedure
{
    public function __construct(
        private readonly Security $security,
        private readonly RegistrationRepository $registrationRepository,
    ) {
    }

    public function execute(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof UserInterface) {
            throw new ApiException('请先登录', -885);
        }

        $registrations = $this->registrationRepository->findBy(['student' => $user]);
        $list = [];
        foreach ($registrations as $registration) {
            $list[] = $registration->retrieveApiArray();
        }

        return [
            'list' => $list,
        ];
    }
}
