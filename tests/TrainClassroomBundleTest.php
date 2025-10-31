<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\TrainClassroomBundle\TrainClassroomBundle;

/**
 * @internal
 */
#[CoversClass(TrainClassroomBundle::class)]
#[RunTestsInSeparateProcesses]
final class TrainClassroomBundleTest extends AbstractBundleTestCase
{
}
