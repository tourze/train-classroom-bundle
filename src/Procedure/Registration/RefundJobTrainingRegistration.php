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
#[MethodExpose(RefundJobTrainingRegistration::NAME)]
#[IsGranted('ROLE_OPERATOR')]
#[MethodPermission(permission: Registration::class . '::renderRefundBtn', title: '退款')]
class RefundJobTrainingRegistration extends ApiCallActionProcedure
{
    public const NAME = 'RefundJobTrainingRegistration';

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
            ->setLabel('退款')
            ->setConfirmText('是否确认要退款？')
            ->setApiName(RefundJobTrainingRegistration::NAME);
    }

    public function execute(): array
    {
        $registration = $this->registrationRepository->findOneBy(['id' => $this->id]);
        if (!$registration) {
            throw new ApiException('找不到记录');
        }

        $price = $registration->getCourse()->getPrice();
        if ($price <= 0) {
            $registration->setRefundTime(Carbon::now());
            $registration->setStatus(OrderStatus::REFUND);
            $this->registrationRepository->save($registration);

            return [
                '__message' => '已退款',
            ];
        }

        try {
            $this->transactionService->decrease(
                'SUPPLIER-REFUND-' . $registration->getId(),
                $this->trainingCreditService->getSupplierAccount($this->security->getUser()),
                $price,
            );
        } catch (\Throwable $exception) {
            $this->procedureLogger->error('退积分失败', [
                'exception' => $exception,
                'price' => $price,
            ]);
            throw new ApiException($exception->getMessage(), previous: $exception);
        }

        $registration->setRefundTime(Carbon::now());
        $registration->setStatus(OrderStatus::REFUND);
        $this->registrationRepository->save($registration);

        return [
            '__message' => '退款成功',
        ];
    }
}
