<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Qrcode;

/**
 * Qrcode实体测试类
 *
 * 测试二维码实体的基本功能，避免外部依赖
 *
 * @internal
 */
#[CoversClass(Qrcode::class)]
final class QrcodeTest extends AbstractEntityTestCase
{
    private Classroom&MockObject $classroom;

    protected function createEntity(): Qrcode
    {
        /*
         * 使用Classroom具体Entity类进行Mock的原因：
         * 1) Classroom是Doctrine实体类，包含复杂的属性和关联关系
         * 2) 测试需要验证Qrcode与Classroom的关联关系，使用具体类确保类型一致
         * 3) Entity类没有对应的接口，使用具体类是唯一选择
         * 4) 在Entity单元测试中模拟关联实体是常见做法，避免数据库依赖
         */
        $this->classroom = $this->createMock(Classroom::class);

        $qrcode = new Qrcode();
        $qrcode->setClassroom($this->classroom);
        $qrcode->setTitle('测试二维码');
        $qrcode->setLimitNumber(100);

        return $qrcode;
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'title' => ['title', '培训签到二维码'];
        yield 'limitNumber_100' => ['limitNumber', 100];
        yield 'limitNumber_0' => ['limitNumber', 0];
        yield 'valid_true' => ['valid', true];
        yield 'valid_false' => ['valid', false];
        yield 'valid_null' => ['valid', null];
        yield 'createdBy_admin' => ['createdBy', 'admin'];
        yield 'createdBy_null' => ['createdBy', null];
        yield 'updatedBy_admin' => ['updatedBy', 'admin'];
        yield 'updatedBy_null' => ['updatedBy', null];
    }

    /**
     * 测试Title的设置和获取
     */
    public function testTitleProperty(): void
    {
        $qrcode = $this->createEntity();
        $title = '培训签到二维码';
        $qrcode->setTitle($title);

        $this->assertSame($title, $qrcode->getTitle());
    }

    /**
     * 测试LimitNumber的设置和获取
     */
    public function testLimitNumberProperty(): void
    {
        $qrcode = $this->createEntity();
        $limitNumber = 50;
        $qrcode->setLimitNumber($limitNumber);

        $this->assertSame($limitNumber, $qrcode->getLimitNumber());
    }

    /**
     * 测试Valid的设置和获取
     */
    public function testValidProperty(): void
    {
        $qrcode = $this->createEntity();
        $qrcode->setValid(true);
        $this->assertTrue($qrcode->isValid());

        $qrcode->setValid(false);
        $this->assertFalse($qrcode->isValid());
    }

    /**
     * 测试Valid的默认值
     */
    public function testValidDefaultValue(): void
    {
        $qrcode = $this->createEntity();
        $this->assertFalse($qrcode->isValid());
    }

    /**
     * 测试CreatedBy的设置和获取
     */
    public function testCreatedByProperty(): void
    {
        $qrcode = $this->createEntity();
        $createdBy = 'admin';
        $qrcode->setCreatedBy($createdBy);

        $this->assertSame($createdBy, $qrcode->getCreatedBy());
    }

    /**
     * 测试CreatedBy可以为null
     */
    public function testCreatedByCanBeNull(): void
    {
        $qrcode = $this->createEntity();
        $qrcode->setCreatedBy(null);

        $this->assertNull($qrcode->getCreatedBy());
    }

    /**
     * 测试UpdatedBy的设置和获取
     */
    public function testUpdatedByProperty(): void
    {
        $qrcode = $this->createEntity();
        $updatedBy = 'admin';
        $qrcode->setUpdatedBy($updatedBy);

        $this->assertSame($updatedBy, $qrcode->getUpdatedBy());
    }

    /**
     * 测试UpdatedBy可以为null
     */
    public function testUpdatedByCanBeNull(): void
    {
        $qrcode = $this->createEntity();
        $qrcode->setUpdatedBy(null);

        $this->assertNull($qrcode->getUpdatedBy());
    }

    /**
     * 测试Classroom关联关系
     */
    public function testClassroomRelationship(): void
    {
        $qrcode = $this->createEntity();
        $this->assertSame($this->classroom, $qrcode->getClassroom());
    }

    /**
     * 测试Registrations集合的初始化
     */
    public function testRegistrationsCollectionInitialization(): void
    {
        $qrcode = $this->createEntity();
        $registrations = $qrcode->getRegistrations();

        $this->assertCount(0, $registrations);
    }

    /**
     * 测试setter方法功能
     */
    public function testSetterMethods(): void
    {
        $qrcode = $this->createEntity();
        $title = '测试二维码修改';
        $limitNumber = 50;
        $createdBy = 'user1';

        $qrcode->setTitle($title);
        $qrcode->setLimitNumber($limitNumber);
        $qrcode->setValid(true);
        $qrcode->setCreatedBy($createdBy);
        $qrcode->setUpdatedBy($createdBy);

        $this->assertSame($title, $qrcode->getTitle());
        $this->assertSame($limitNumber, $qrcode->getLimitNumber());
        $this->assertTrue($qrcode->isValid());
        $this->assertSame($createdBy, $qrcode->getCreatedBy());
        $this->assertSame($createdBy, $qrcode->getUpdatedBy());
    }

    /**
     * 测试所有可选属性的默认值
     */
    public function testOptionalPropertiesDefaultValues(): void
    {
        $qrcode = $this->createEntity();
        // title和limitNumber是必填属性，在createEntity中已设置
        $this->assertSame('测试二维码', $qrcode->getTitle());
        $this->assertSame(100, $qrcode->getLimitNumber());
        $this->assertSame($this->classroom, $qrcode->getClassroom());
        $this->assertNull($qrcode->getCreatedBy());
        $this->assertNull($qrcode->getUpdatedBy());
        $this->assertFalse($qrcode->isValid());
    }

    /**
     * 测试边界值情况
     */
    public function testBoundaryValues(): void
    {
        $qrcode = $this->createEntity();

        // 测试空字符串标题
        $qrcode->setTitle('');
        $this->assertSame('', $qrcode->getTitle());

        // 测试长字符串标题
        $longTitle = str_repeat('这是一个很长的标题', 50);
        $qrcode->setTitle($longTitle);
        $this->assertSame($longTitle, $qrcode->getTitle());

        // 测试限制数量边界值
        $qrcode->setLimitNumber(0);
        $this->assertSame(0, $qrcode->getLimitNumber());

        $qrcode->setLimitNumber(PHP_INT_MAX);
        $this->assertSame(PHP_INT_MAX, $qrcode->getLimitNumber());

        // 测试负数限制数量
        $qrcode->setLimitNumber(-1);
        $this->assertSame(-1, $qrcode->getLimitNumber());
    }

    /**
     * 测试集合操作
     */
    public function testCollectionOperations(): void
    {
        $qrcode = $this->createEntity();
        // 测试集合初始状态
        $this->assertCount(0, $qrcode->getRegistrations());
    }
}
