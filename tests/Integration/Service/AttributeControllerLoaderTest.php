<?php

namespace Tourze\TrainClassroomBundle\Tests\Integration\Service;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Service\AttributeControllerLoader;

class AttributeControllerLoaderTest extends TestCase
{
    public function testServiceExists(): void
    {
        $this->assertTrue(class_exists(AttributeControllerLoader::class));
    }
}