<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Param;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\TrainClassroomBundle\Param\GetJobTrainingJoinedClassroomListParam;

/**
 * @internal
 */
#[CoversClass(GetJobTrainingJoinedClassroomListParam::class)]
#[RunTestsInSeparateProcesses]
final class GetJobTrainingJoinedClassroomListParamTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // Param测试不需要额外的设置
    }

    public function testParamCanBeConstructed(): void
    {
        $param = self::getService(GetJobTrainingJoinedClassroomListParam::class);

        $this->assertInstanceOf(RpcParamInterface::class, $param);
    }
}
