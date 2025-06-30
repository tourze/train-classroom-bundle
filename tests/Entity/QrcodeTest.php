<?php

namespace Tourze\TrainClassroomBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tourze\TrainClassroomBundle\Entity\Qrcode;

/**
 * Qrcode实体测试类
 *
 * 测试二维码实体的基本功能，避免外部依赖
 */
class QrcodeTest extends TestCase
{
    private Qrcode $qrcode;

    protected function setUp(): void
    {
        $this->qrcode = new Qrcode();
    }

    /**
     * 测试Title的设置和获取
     */
    public function test_title_property(): void
    {
        $title = '培训签到二维码';
        $this->qrcode->setTitle($title);
        
        $this->assertSame($title, $this->qrcode->getTitle());
    }

    /**
     * 测试LimitNumber的设置和获取
     */
    public function test_limit_number_property(): void
    {
        $limitNumber = 100;
        $this->qrcode->setLimitNumber($limitNumber);
        
        $this->assertSame($limitNumber, $this->qrcode->getLimitNumber());
    }

    /**
     * 测试Valid的设置和获取
     */
    public function test_valid_property(): void
    {
        $this->qrcode->setValid(true);
        $this->assertTrue($this->qrcode->isValid());
        
        $this->qrcode->setValid(false);
        $this->assertFalse($this->qrcode->isValid());
    }

    /**
     * 测试Valid的默认值
     */
    public function test_valid_default_value(): void
    {
        $this->assertFalse($this->qrcode->isValid());
    }

    /**
     * 测试CreatedBy的设置和获取
     */
    public function test_created_by_property(): void
    {
        $createdBy = 'admin';
        $this->qrcode->setCreatedBy($createdBy);
        
        $this->assertSame($createdBy, $this->qrcode->getCreatedBy());
    }

    /**
     * 测试CreatedBy可以为null
     */
    public function test_created_by_can_be_null(): void
    {
        $this->qrcode->setCreatedBy(null);
        
        $this->assertNull($this->qrcode->getCreatedBy());
    }

    /**
     * 测试UpdatedBy的设置和获取
     */
    public function test_updated_by_property(): void
    {
        $updatedBy = 'admin';
        $this->qrcode->setUpdatedBy($updatedBy);
        
        $this->assertSame($updatedBy, $this->qrcode->getUpdatedBy());
    }

    /**
     * 测试UpdatedBy可以为null
     */
    public function test_updated_by_can_be_null(): void
    {
        $this->qrcode->setUpdatedBy(null);
        
        $this->assertNull($this->qrcode->getUpdatedBy());
    }

    /**
     * 测试Registrations集合的初始化
     */
    public function test_registrations_collection_initialization(): void
    {
        $registrations = $this->qrcode->getRegistrations();
        
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $registrations);
        $this->assertCount(0, $registrations);
    }

    /**
     * 测试方法链式调用
     */
    public function test_method_chaining(): void
    {
        $title = '链式调用测试';
        $limitNumber = 50;
        
        $result = $this->qrcode
            ->setTitle($title)
            ->setLimitNumber($limitNumber)
            ->setValid(false);
        
        $this->assertSame($this->qrcode, $result);
        $this->assertSame($title, $this->qrcode->getTitle());
        $this->assertSame($limitNumber, $this->qrcode->getLimitNumber());
        $this->assertFalse($this->qrcode->isValid());
    }

    /**
     * 测试边界值
     */
    public function test_boundary_values(): void
    {
        // 测试空字符串
        $this->qrcode->setTitle('');
        $this->assertSame('', $this->qrcode->getTitle());
        
        // 测试长字符串
        $longTitle = str_repeat('A', 1000);
        $this->qrcode->setTitle($longTitle);
        $this->assertSame($longTitle, $this->qrcode->getTitle());
        
        // 测试负数限制
        $this->qrcode->setLimitNumber(-1);
        $this->assertSame(-1, $this->qrcode->getLimitNumber());
        
        // 测试零限制
        $this->qrcode->setLimitNumber(0);
        $this->assertSame(0, $this->qrcode->getLimitNumber());
    }

    /**
     * 测试时间属性默认值
     */
    public function test_time_properties_default_values(): void
    {
        $this->assertNull($this->qrcode->getCreateTime());
        $this->assertNull($this->qrcode->getUpdateTime());
    }

    /**
     * 测试用户属性默认值
     */
    public function test_user_properties_default_values(): void
    {
        $this->assertNull($this->qrcode->getCreatedBy());
        $this->assertNull($this->qrcode->getUpdatedBy());
    }
} 