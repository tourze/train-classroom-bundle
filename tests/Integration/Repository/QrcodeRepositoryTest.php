<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Repository;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Repository\QrcodeRepository;

class QrcodeRepositoryTest extends TestCase
{
    public function testRepositoryExists(): void
    {
        $this->assertTrue(class_exists(QrcodeRepository::class));
    }
}