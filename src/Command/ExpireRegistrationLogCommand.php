<?php

namespace Tourze\TrainClassroomBundle\Command;

use Carbon\CarbonImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

#[AsCronTask('* * * * *')]
#[AsCommand(name: self::NAME, description: '过期无效的报班记录')]
class ExpireRegistrationLogCommand extends Command
{
    protected const NAME = 'job-training:expire-registration';
    public function __construct(
        private readonly RegistrationRepository $registrationRepository,
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
            ->getResult();
        foreach ($registrations as $registration) {
            assert($registration instanceof Registration);
            $registration->setExpired(true);
            $this->registrationRepository->save($registration);
        }

        return Command::SUCCESS;
    }
}
