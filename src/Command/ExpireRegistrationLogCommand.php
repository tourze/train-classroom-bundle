<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

#[AsCronTask(expression: '* * * * *')]
#[AsCommand(name: self::NAME, description: '过期无效的报班记录')]
class ExpireRegistrationLogCommand extends Command
{
    protected const NAME = 'job-training:expire-registration';

    public function __construct(
        private readonly RegistrationRepository $registrationRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $registrations = $this->registrationRepository
            ->createQueryBuilder('a')
            ->where('a.endTime<:now AND a.expired=false AND a.finished=false')
            ->setParameter('now', CarbonImmutable::now())
            ->getQuery()
            ->getResult()
        ;

        if (!\is_array($registrations)) {
            return Command::SUCCESS;
        }

        foreach ($registrations as $registration) {
            assert($registration instanceof Registration);
            $registration->setExpired(true);
            $this->entityManager->persist($registration);
        }

        if (\count($registrations) > 0) {
            $this->entityManager->flush();
        }

        return Command::SUCCESS;
    }
}
