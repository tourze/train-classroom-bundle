<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Command;

use Carbon\CarbonImmutable;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Command\ExpireRegistrationLogCommand;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

class ExpireRegistrationLogCommandTest extends TestCase
{
    private ExpireRegistrationLogCommand $command;
    private RegistrationRepository $registrationRepository;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->registrationRepository = $this->createMock(RegistrationRepository::class);
        $this->command = new ExpireRegistrationLogCommand($this->registrationRepository);
        
        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecuteWithExpiredRegistrations(): void
    {
        // 创建测试数据
        $expiredRegistration = $this->createMock(Registration::class);
        $expiredRegistration->expects($this->once())->method('setExpired')->with(true);
        
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        $query->expects($this->once())->method('getResult')->willReturn([$expiredRegistration]);
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);
        
        $this->registrationRepository->expects($this->once())
            ->method('createQueryBuilder')->willReturn($queryBuilder);
        $this->registrationRepository->expects($this->once())
            ->method('save')->with($expiredRegistration);
        
        // 执行命令  
        $exitCode = $this->commandTester->execute([]);
        
        // 断言
        $this->assertEquals(0, $exitCode);
    }

    public function testExecuteWithNoExpiredRegistrations(): void
    {
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        $query->expects($this->once())->method('getResult')->willReturn([]);
        $queryBuilder->expects($this->once())->method('where')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('setParameter')->willReturnSelf();
        $queryBuilder->expects($this->once())->method('getQuery')->willReturn($query);
        
        $this->registrationRepository->expects($this->once())
            ->method('createQueryBuilder')->willReturn($queryBuilder);
        $this->registrationRepository->expects($this->never())->method('save');
        
        // 执行命令
        $exitCode = $this->commandTester->execute([]);
        
        // 断言
        $this->assertEquals(0, $exitCode);
    }
}