<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\TrainClassroomBundle\Service\AdminMenu;

/**
 * AdminMenu服务测试
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // Setup for AdminMenu tests
    }

    public function testClassImplementsMenuProviderInterface(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->implementsInterface(MenuProviderInterface::class));
    }

    public function testClassIsReadonly(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testHasAutoconfigureAttribute(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $attributes = $reflection->getAttributes(Autoconfigure::class);

        $this->assertCount(1, $attributes);
        $attributeArgs = $attributes[0]->getArguments();
        $this->assertArrayHasKey('public', $attributeArgs);
        $this->assertTrue($attributeArgs['public']);
    }

    public function testHasInvokeMethod(): void
    {
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->hasMethod('__invoke'));

        $method = $reflection->getMethod('__invoke');
        $this->assertTrue($method->isPublic());

        $params = $method->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('item', $params[0]->getName());
    }

    public function testInvokeCreatesMenuStructure(): void
    {
        $container = self::getContainer();
        /** @var AdminMenu $adminMenu */
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        // Explicit method call to avoid PHPStan dynamic call warning
        $adminMenu->__invoke($rootItem);

        // 验证创建了顶级菜单
        $trainMenu = $rootItem->getChild('培训教室管理');
        self::assertNotNull($trainMenu);

        // 验证图标属性
        self::assertSame('fas fa-chalkboard-teacher', $trainMenu->getAttribute('icon'));
    }

    public function testInvokeCreatesAllSubMenuItems(): void
    {
        $container = self::getContainer();
        /** @var AdminMenu $adminMenu */
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        $adminMenu->__invoke($rootItem);

        $trainMenu = $rootItem->getChild('培训教室管理');
        self::assertNotNull($trainMenu);

        // 验证所有子菜单项
        self::assertNotNull($trainMenu->getChild('班级管理'));
        self::assertNotNull($trainMenu->getChild('报名管理'));
        self::assertNotNull($trainMenu->getChild('二维码管理'));
        self::assertNotNull($trainMenu->getChild('排课管理'));
        self::assertNotNull($trainMenu->getChild('考勤记录'));
    }

    public function testMenuItemsHaveCorrectIcons(): void
    {
        $container = self::getContainer();
        /** @var AdminMenu $adminMenu */
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        $adminMenu->__invoke($rootItem);

        $trainMenu = $rootItem->getChild('培训教室管理');
        self::assertNotNull($trainMenu);

        // 验证图标
        $classroomItem = $trainMenu->getChild('班级管理');
        self::assertNotNull($classroomItem);
        self::assertSame('fas fa-users', $classroomItem->getAttribute('icon'));

        $registrationItem = $trainMenu->getChild('报名管理');
        self::assertNotNull($registrationItem);
        self::assertSame('fas fa-user-plus', $registrationItem->getAttribute('icon'));

        $qrcodeItem = $trainMenu->getChild('二维码管理');
        self::assertNotNull($qrcodeItem);
        self::assertSame('fas fa-qrcode', $qrcodeItem->getAttribute('icon'));

        $scheduleItem = $trainMenu->getChild('排课管理');
        self::assertNotNull($scheduleItem);
        self::assertSame('fas fa-calendar-alt', $scheduleItem->getAttribute('icon'));

        $attendanceItem = $trainMenu->getChild('考勤记录');
        self::assertNotNull($attendanceItem);
        self::assertSame('fas fa-clipboard-check', $attendanceItem->getAttribute('icon'));
    }

    public function testMenuItemsHaveCorrectUris(): void
    {
        $container = self::getContainer();
        /** @var AdminMenu $adminMenu */
        $adminMenu = $container->get(AdminMenu::class);
        self::assertInstanceOf(AdminMenu::class, $adminMenu);

        $factory = new MenuFactory();
        $rootItem = $factory->createItem('root');

        $adminMenu->__invoke($rootItem);

        $trainMenu = $rootItem->getChild('培训教室管理');
        self::assertNotNull($trainMenu);

        // 验证生成了正确的URI
        $classroomItem = $trainMenu->getChild('班级管理');
        self::assertNotNull($classroomItem);
        self::assertNotNull($classroomItem->getUri());
    }

    public function testControllerNamespace(): void
    {
        $this->assertEquals(
            'Tourze\TrainClassroomBundle\Service',
            (new \ReflectionClass(AdminMenu::class))->getNamespaceName()
        );
    }
}
