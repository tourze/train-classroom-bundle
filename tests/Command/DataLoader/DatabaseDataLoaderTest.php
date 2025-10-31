<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Command\DataLoader;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Command\DataLoader\DatabaseDataLoader;
use Tourze\TrainClassroomBundle\Command\DataLoader\DataLoaderInterface;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;

/**
 * DatabaseDataLoader测试类
 *
 * 测试数据库数据加载器的基本功能
 *
 * @internal
 */
#[CoversClass(DatabaseDataLoader::class)]
#[RunTestsInSeparateProcesses]
final class DatabaseDataLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 不需要数据库操作
    }

    /**
     * 测试类可以被实例化
     */
    public function testCanBeInstantiated(): void
    {
        $loader = self::getService(DatabaseDataLoader::class);
        $this->assertInstanceOf(DatabaseDataLoader::class, $loader);
        $this->assertInstanceOf(DataLoaderInterface::class, $loader);
    }

    /**
     * 测试实现DataLoaderInterface接口
     */
    public function testImplementsDataLoaderInterface(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $this->assertTrue($reflection->implementsInterface(DataLoaderInterface::class));
    }

    /**
     * 测试supports方法对database源返回true
     */
    public function testSupportsDatabaseSource(): void
    {
        $loader = self::getService(DatabaseDataLoader::class);
        $this->assertTrue($loader->supports('database'));
    }

    /**
     * 测试supports方法对非database源返回false
     */
    public function testDoesNotSupportNonDatabaseSource(): void
    {
        $loader = self::getService(DatabaseDataLoader::class);
        $this->assertFalse($loader->supports('api'));
        $this->assertFalse($loader->supports('file'));
        $this->assertFalse($loader->supports('other'));
    }

    /**
     * 测试load方法存在且可调用
     */
    public function testLoadMethodExists(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $this->assertTrue($reflection->hasMethod('load'));

        $method = $reflection->getMethod('load');
        $this->assertTrue($method->isPublic());
        $this->assertCount(2, $method->getParameters());
    }

    /**
     * 测试load方法缺少database-dsn参数时抛出异常
     */
    public function testLoadThrowsExceptionWhenDsnMissing(): void
    {
        $loader = self::getService(DatabaseDataLoader::class);

        // 创建带有选项定义的输入
        $definition = new InputDefinition([
            new InputOption('database-dsn', null, InputOption::VALUE_OPTIONAL),
        ]);

        $input = new StringInput('');
        $input->bind($definition);

        $output = new BufferedOutput();
        $io = new SymfonyStyle($input, $output);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('使用数据库数据源时必须指定 --database-dsn 参数');

        $loader->load($input, $io);
    }

    /**
     * 测试load方法返回空数组（功能未实现）
     */
    public function testLoadReturnsEmptyArray(): void
    {
        $loader = self::getService(DatabaseDataLoader::class);

        // 创建带有选项定义的输入
        $definition = new InputDefinition([
            new InputOption('database-dsn', null, InputOption::VALUE_OPTIONAL),
        ]);

        $input = new StringInput('--database-dsn=mysql://localhost/test');
        $input->bind($definition);

        $output = new BufferedOutput();
        $io = new SymfonyStyle($input, $output);

        $result = $loader->load($input, $io);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * 测试类的方法可见性
     */
    public function testMethodVisibility(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);

        // 测试公共方法
        $this->assertTrue($reflection->hasMethod('supports'));
        $supportsMethod = $reflection->getMethod('supports');
        $this->assertTrue($supportsMethod->isPublic());

        $this->assertTrue($reflection->hasMethod('load'));
        $loadMethod = $reflection->getMethod('load');
        $this->assertTrue($loadMethod->isPublic());
    }

    /**
     * 测试supports方法参数类型
     */
    public function testSupportsMethodParameters(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $method = $reflection->getMethod('supports');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('source', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
    }

    /**
     * 测试load方法参数类型
     */
    public function testLoadMethodParameters(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $method = $reflection->getMethod('load');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('input', $parameters[0]->getName());
        $this->assertEquals('io', $parameters[1]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertTrue($parameters[1]->hasType());
    }

    /**
     * 测试load方法返回类型
     */
    public function testLoadMethodReturnType(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $method = $reflection->getMethod('load');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    /**
     * 测试supports方法返回类型
     */
    public function testSupportsMethodReturnType(): void
    {
        $reflection = new \ReflectionClass(DatabaseDataLoader::class);
        $method = $reflection->getMethod('supports');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals('bool', $returnType->getName());
    }
}
