<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    /**
     * 测试初始化和实例化
     */
    public function testServiceInstantiation(): void
    {
        $loader = self::getService(AttributeControllerLoader::class);
        $this->assertInstanceOf(AttributeControllerLoader::class, $loader);
    }

    public function testAutoload(): void
    {
        $result = $this->loader->autoload();

        $this->assertInstanceOf(RouteCollection::class, $result);

        // 验证方法参数数量（无参数）
        $reflection = new \ReflectionMethod($this->loader, 'autoload');
        $this->assertCount(0, $reflection->getParameters());

        // 验证返回类型
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame('Symfony\Component\Routing\RouteCollection', (string) $returnType);
    }

    public function testLoad(): void
    {
        $result = $this->loader->load('some_resource');

        $this->assertInstanceOf(RouteCollection::class, $result);

        // 验证方法参数数量（2个参数：$resource 和 $type）
        $reflection = new \ReflectionMethod($this->loader, 'load');
        $this->assertCount(2, $reflection->getParameters());

        // 验证返回类型
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame('Symfony\Component\Routing\RouteCollection', (string) $returnType);

        // 测试带有type参数的调用
        $result = $this->loader->load('some_resource', 'some_type');
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupports(): void
    {
        // supports方法应始终返回false
        $result = $this->loader->supports('some_resource');
        $this->assertFalse($result);

        $result = $this->loader->supports('some_resource', 'some_type');
        $this->assertFalse($result);

        // 验证方法参数数量（2个参数：$resource 和 $type）
        $reflection = new \ReflectionMethod($this->loader, 'supports');
        $this->assertCount(2, $reflection->getParameters());

        // 验证返回类型
        $returnType = $reflection->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertSame('bool', (string) $returnType);
    }
}
