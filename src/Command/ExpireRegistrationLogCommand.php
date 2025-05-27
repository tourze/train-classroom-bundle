<?php

namespace Tourze\TrainClassroomBundle\Command;

use Carbon\Carbon;
use SenboTrainingBundle\Entity\Registration;
use SenboTrainingBundle\Repository\RegistrationRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask('* * * * *')]
#[AsCommand(name: 'job-training:expire-registration', description: '过期无效的报班记录')]
class ExpireRegistrationLogCommand extends Command
{
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
            ->setParameter('now', Carbon::now())
            ->getQuery()
            ->getResult();
        foreach ($registrations as $registration) {
            /* @var Registration $registration */
            $registration->setExpired(true);
            $this->registrationRepository->save($registration);
        }

        return Command::SUCCESS;
    }
}
