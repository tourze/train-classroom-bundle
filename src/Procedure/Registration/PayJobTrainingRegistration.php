<?php

namespace SenboTrainingBundle\Procedure\Registration;

use AntdCpBundle\Builder\Action\ApiCallAction;
use AppBundle\Procedure\Base\ApiCallActionProcedure;
use Carbon\Carbon;
use CreditBundle\Service\TransactionService;
use Psr\Log\LoggerInterface;
use SenboTrainingBundle\Entity\Registration;
use SenboTrainingBundle\Enum\OrderStatus;
use SenboTrainingBundle\Repository\RegistrationRepository;
use SenboTrainingBundle\Service\TrainingCreditService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPCLogBundle\Attribute\Log;
use Tourze\JsonRPCSecurityBundle\Attribute\MethodPermission;

#[Log]
#[MethodExpose(PayJobTrainingRegistration::NAME)]
#[IsGranted('ROLE_OPERATOR')]
#[MethodPermission(permission: Registration::class . '::renderPayBtn', title: '支付')]
class PayJobTrainingRegistration extends ApiCallActionProcedure
{
    public const NAME = 'PayJobTrainingRegistration';

    public function __construct(
        private readonly TrainingCreditService $trainingCreditService,
        private readonly TransactionService $transactionService,
        private readonly RegistrationRepository $registrationRepository,
        private readonly Security $security,
        private readonly LoggerInterface $procedureLogger,
    ) {
    }

    public function getAction(): ApiCallAction
    {
        return ApiCallAction::gen()
            ->setLabel('支付')
            ->setConfirmText('确定已支付？')
            ->setApiName(PayJobTrainingRegistration::NAME);
    }

    public function execute(): array
    {
        $registration = $this->registrationRepository->findOneBy(['id' => $this->id]);
        if (!$registration) {
            throw new ApiException('找不到记录');
        }

        $price = $registration->getCourse()->getPrice();
        if ($price <= 0) {
            $registration->setPayTime(Carbon::now());
            $registration->setStatus(OrderStatus::PAID);
            $this->registrationRepository->save($registration);

            return [
                '__message' => '已支付',
            ];
        }

        try {
            $this->transactionService->increase(
                'SUPPLIER-INCOME-' . $registration->getId(),
                $this->trainingCreditService->getSupplierAccount($this->security->getUser()),
                $price,
            );
        } catch (\Throwable $exception) {
            $this->procedureLogger->error('支付积分失败', [
                'exception' => $exception,
                'price' => $price,
            ]);
            throw new ApiException($exception->getMessage(), previous: $exception);
        }

        $registration->setPayTime(Carbon::now());
        $registration->setStatus(OrderStatus::PAID);
        $this->registrationRepository->save($registration);

        return [
            '__message' => '支付成功',
        ];
    }
}
