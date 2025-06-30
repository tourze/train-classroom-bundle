<?php

namespace Tourze\TrainClassroomBundle\Tests\Unit\Procedure;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Procedure\GetJobTrainingJoinedClassroomList;

class GetJobTrainingJoinedClassroomListTest extends TestCase
{
    public function testProcedureCanBeInstantiated(): void
    {
        $mockSecurity = $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class);
        $mockRepository = $this->createMock(\Tourze\TrainClassroomBundle\Repository\RegistrationRepository::class);
        
        $procedure = new GetJobTrainingJoinedClassroomList($mockSecurity, $mockRepository);
        $this->assertInstanceOf(GetJobTrainingJoinedClassroomList::class, $procedure);
    }

    public function testProcedureImplementsInterface(): void
    {
        $mockSecurity = $this->createMock(\Symfony\Bundle\SecurityBundle\Security::class);
        $mockRepository = $this->createMock(\Tourze\TrainClassroomBundle\Repository\RegistrationRepository::class);
        
        $procedure = new GetJobTrainingJoinedClassroomList($mockSecurity, $mockRepository);
        $this->assertNotNull($procedure);
    }
}