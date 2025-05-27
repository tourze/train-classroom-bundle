<?php

namespace Tourze\TrainClassroomBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tourze\TrainClassroomBundle\Command\CleanupDataCommand;

/**
 * CleanupDataCommand测试类
 * 
 * 测试命令类的基本功能和配置
 */
class CleanupDataCommandTest extends TestCase
{
    private CleanupDataCommand $command;
    private EntityManagerInterface&MockObject $entityManager;
    private ParameterBagInterface&MockObject $parameterBag;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->parameterBag = $this->createMock(ParameterBagInterface::class);

        $this->command = new CleanupDataCommand(
            $this->entityManager,
            $this->parameterBag
        );

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    /**
     * 测试命令基本信息
     */
    public function test_command_configuration(): void
    {
        $this->assertEquals('train-classroom:cleanup-data', $this->command->getName());
        $this->assertStringContainsString('清理过期的考勤记录', $this->command->getDescription());
    }

    /**
     * 测试命令类存在
     */
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(CleanupDataCommand::class));
    }

    /**
     * 测试命令继承正确的父类
     */
    public function test_command_extends_command(): void
    {
        $reflection = new \ReflectionClass(CleanupDataCommand::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    /**
     * 测试命令选项配置
     */
    public function test_command_options(): void
    {
        $definition = $this->command->getDefinition();
        
        // 测试选项存在
        $this->assertTrue($definition->hasOption('attendance-days'));
        $this->assertTrue($definition->hasOption('video-days'));
        $this->assertTrue($definition->hasOption('schedule-days'));
        $this->assertTrue($definition->hasOption('dry-run'));
        $this->assertTrue($definition->hasOption('force'));
        $this->assertTrue($definition->hasOption('batch-size'));
    }

    /**
     * 测试构造函数参数
     */
    public function test_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(CleanupDataCommand::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);
        
        // 验证参数名称
        $this->assertEquals('entityManager', $parameters[0]->getName());
        $this->assertEquals('parameterBag', $parameters[1]->getName());
    }

    /**
     * 测试execute方法存在
     */
    public function test_execute_method_exists(): void
    {
        $this->assertTrue(method_exists(CleanupDataCommand::class, 'execute'));
    }

    /**
     * 测试configure方法存在
     */
    public function test_configure_method_exists(): void
    {
        $this->assertTrue(method_exists(CleanupDataCommand::class, 'configure'));
    }

    /**
     * 测试命令帮助信息
     */
    public function test_command_help(): void
    {
        $help = $this->command->getHelp();
        $this->assertNotEmpty($help);
        $this->assertStringContainsString('清理过期的培训数据', $help);
    }

    /**
     * 测试dry-run选项
     */
    public function test_dry_run_option(): void
    {
        $definition = $this->command->getDefinition();
        $option = $definition->getOption('dry-run');
        
        $this->assertFalse($option->acceptValue());
        $this->assertStringContainsString('试运行', $option->getDescription());
    }

    /**
     * 测试force选项
     */
    public function test_force_option(): void
    {
        $definition = $this->command->getDefinition();
        $option = $definition->getOption('force');
        
        $this->assertFalse($option->acceptValue());
        $this->assertStringContainsString('强制执行', $option->getDescription());
    }

    /**
     * 测试batch-size选项
     */
    public function test_batch_size_option(): void
    {
        $definition = $this->command->getDefinition();
        $option = $definition->getOption('batch-size');
        
        $this->assertTrue($option->acceptValue());
        $this->assertEquals('1000', $option->getDefault());
    }

    /**
     * 测试命令实例化
     */
    public function test_command_instantiation(): void
    {
        $this->assertInstanceOf(CleanupDataCommand::class, $this->command);
        $this->assertInstanceOf(Command::class, $this->command);
    }

    /**
     * 测试命令名称格式
     */
    public function test_command_name_format(): void
    {
        $name = $this->command->getName();
        $this->assertMatchesRegularExpression('/^train-classroom:[a-z-]+$/', $name);
    }

    /**
     * 测试命令描述不为空
     */
    public function test_command_description_not_empty(): void
    {
        $description = $this->command->getDescription();
        $this->assertNotEmpty($description);
        $this->assertIsString($description);
    }

    /**
     * 测试选项默认值
     */
    public function test_option_defaults(): void
    {
        $definition = $this->command->getDefinition();
        
        // 测试schedule-days默认值
        $scheduleOption = $definition->getOption('schedule-days');
        $this->assertEquals('90', $scheduleOption->getDefault());
        
        // 测试batch-size默认值
        $batchOption = $definition->getOption('batch-size');
        $this->assertEquals('1000', $batchOption->getDefault());
    }

    /**
     * 测试命令别名
     */
    public function test_command_aliases(): void
    {
        $aliases = $this->command->getAliases();
        $this->assertIsArray($aliases);
    }

    /**
     * 测试私有方法存在
     */
    public function test_private_methods_exist(): void
    {
        $reflection = new \ReflectionClass(CleanupDataCommand::class);
        
        // 测试关键私有方法存在
        $this->assertTrue($reflection->hasMethod('getCleanupConfig'));
        $this->assertTrue($reflection->hasMethod('cleanupAttendanceRecords'));
        $this->assertTrue($reflection->hasMethod('cleanupScheduleRecords'));
        $this->assertTrue($reflection->hasMethod('cleanupVideoFiles'));
    }

    /**
     * 测试命令属性
     */
    public function test_command_attributes(): void
    {
        $reflection = new \ReflectionClass(CleanupDataCommand::class);
        $attributes = $reflection->getAttributes();
        
        $this->assertNotEmpty($attributes);
        
        // 检查是否有AsCommand属性
        $hasAsCommand = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'AsCommand')) {
                $hasAsCommand = true;
                break;
            }
        }
        $this->assertTrue($hasAsCommand);
    }
} 