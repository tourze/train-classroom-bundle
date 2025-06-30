<?php

namespace Tourze\TrainClassroomBundle\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Tourze\TrainClassroomBundle\Command\UpdateScheduleStatusCommand;

/**
 * UpdateScheduleStatusCommand测试类
 *
 * 测试排课状态更新命令的基本功能
 */
class UpdateScheduleStatusCommandTest extends TestCase
{
    /**
     * 测试命令类存在
     */
    public function test_command_class_exists(): void
    {
        $this->assertTrue(class_exists(UpdateScheduleStatusCommand::class));
    }

    /**
     * 测试命令继承正确的父类
     */
    public function test_command_extends_command(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $this->assertTrue($reflection->isSubclassOf(Command::class));
    }

    /**
     * 测试execute方法存在
     */
    public function test_execute_method_exists(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $this->assertTrue($reflection->hasMethod('execute'));
    }

    /**
     * 测试configure方法存在
     */
    public function test_configure_method_exists(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $this->assertTrue($reflection->hasMethod('configure'));
    }

    /**
     * 测试构造函数存在
     */
    public function test_constructor_exists(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
    }

    /**
     * 测试命令属性
     */
    public function test_command_attributes(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
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

    /**
     * 测试私有方法存在
     */
    public function test_private_methods_exist(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        
        // 测试关键私有方法存在
        $this->assertTrue($reflection->hasMethod('updateScheduledToInProgress'));
        $this->assertTrue($reflection->hasMethod('updateInProgressToCompleted'));
        $this->assertTrue($reflection->hasMethod('updateScheduledToCompleted'));
    }

    /**
     * 测试方法可见性
     */
    public function test_method_visibility(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        
        // 测试公共方法
        $executeMethod = $reflection->getMethod('execute');
        $this->assertTrue($executeMethod->isProtected());
        
        $configureMethod = $reflection->getMethod('configure');
        $this->assertTrue($configureMethod->isProtected());
        
        // 测试私有方法
        $updateMethod = $reflection->getMethod('updateScheduledToInProgress');
        $this->assertTrue($updateMethod->isPrivate());
    }

    /**
     * 测试类属性
     */
    public function test_class_properties(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $properties = $reflection->getProperties();
        
        $this->assertNotEmpty($properties);
        
        // 验证关键属性存在
        $propertyNames = array_map(fn($prop) => $prop->getName(), $properties);
        $this->assertContains('entityManager', $propertyNames);
        $this->assertContains('scheduleRepository', $propertyNames);
        $this->assertContains('logger', $propertyNames);
    }

    /**
     * 测试方法参数
     */
    public function test_method_parameters(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        
        // 测试execute方法参数
        $executeMethod = $reflection->getMethod('execute');
        $parameters = $executeMethod->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertEquals('input', $parameters[0]->getName());
        $this->assertEquals('output', $parameters[1]->getName());
    }

    /**
     * 测试返回类型
     */
    public function test_return_types(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        
        // 测试execute方法返回类型
        $executeMethod = $reflection->getMethod('execute');
        $returnType = $executeMethod->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('int', (string) $returnType);
    }

    /**
     * 测试状态更新方法
     */
    public function test_status_update_methods(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        
        // 验证状态更新方法存在
        $updateMethods = [
            'updateScheduledToInProgress',
            'updateInProgressToCompleted',
            'updateScheduledToCompleted'
        ];
        
        foreach ($updateMethods as $methodName) {
            $this->assertTrue(
                $reflection->hasMethod($methodName),
                "Status update method missing: $methodName"
            );
        }
    }

    /**
     * 测试构造函数参数
     */
    public function test_constructor_parameters(): void
    {
        $reflection = new \ReflectionClass(UpdateScheduleStatusCommand::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(2, count($parameters));
    }

} 