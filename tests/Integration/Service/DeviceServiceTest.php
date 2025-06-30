<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\DeviceService;

class DeviceServiceTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(DeviceService::class));
    }
}