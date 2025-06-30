<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;

class RegistrationRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(RegistrationRepository::class));
    }
}