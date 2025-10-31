<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Repository\ClassroomRepository;
use Tourze\TrainClassroomBundle\Repository\ClassroomScheduleRepository;
use Tourze\TrainClassroomBundle\Service\ClassroomService;
use Tourze\TrainClassroomBundle\Service\ClassroomServiceInterface;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * ClassroomService测试类
 *
 * 测试服务类的基本功能和接口实现
 *
 * @internal
 */
#[CoversClass(ClassroomService::class)]
#[RunTestsInSeparateProcesses]
final class ClassroomServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 初始化测试数据库环境
    }

    /**
     * 创建测试用的分类类型
     */
    private function createCatalogType(): CatalogType
    {
        $catalogType = new CatalogType();
        $catalogType->setCode('test-type-' . uniqid());
        $catalogType->setName('测试分类类型');

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($catalogType);
        $entityManager->flush();

        return $catalogType;
    }

    /**
     * 创建测试用的分类
     */
    private function createTestCategory(): Catalog
    {
        $catalogType = $this->createCatalogType();

        $category = new Catalog();
        $category->setName('测试分类');
        $category->setType($catalogType);

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($category);
        $entityManager->flush();

        return $category;
    }

    /**
     * 创建测试用的课程
     */
    private function createTestCourse(Catalog $category): Course
    {
        $course = new Course();
        $course->setTitle('测试课程');
        $course->setCategory($category);
        $course->setLearnHour(40); // 设置必需的学时

        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($course);
        $entityManager->flush();

        return $course;
    }

    /**
     * 测试createClassroom方法 - 创建教室
     */
    public function testCreateClassroom(): void
    {
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);
        $service = self::getService(ClassroomService::class);

        $data = [
            'name' => '测试教室',
            'location' => '一楼101',
            'capacity' => 50,
            'type' => 'PHYSICAL',
            'category_id' => $category->getId(),
            'course_id' => $course->getId(),
        ];

        $result = $service->createClassroom($data);
        $this->assertInstanceOf(Classroom::class, $result);
    }

    public function testServiceImplementsInterface(): void
    {
        $reflection = new \ReflectionClass(ClassroomService::class);
        $this->assertTrue($reflection->implementsInterface(ClassroomServiceInterface::class));
    }

    public function testServiceHasAutoconfigureAttribute(): void
    {
        $reflection = new \ReflectionClass(ClassroomService::class);
        $attributes = $reflection->getAttributes(Autoconfigure::class);

        $this->assertCount(1, $attributes);

        $autoconfigureAttribute = $attributes[0]->newInstance();
        $this->assertTrue($autoconfigureAttribute->public);
    }

    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(ClassroomService::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertGreaterThanOrEqual(5, count($parameters));

        // 验证构造函数参数名称和类型
        $parameterNames = array_map(fn ($param) => $param->getName(), $parameters);
        $this->assertContains('entityManager', $parameterNames);
        $this->assertContains('classroomRepository', $parameterNames);
        $this->assertContains('scheduleRepository', $parameterNames);
        $this->assertContains('logger', $parameterNames);
        $this->assertContains('security', $parameterNames);

        // 验证参数类型
        foreach ($parameters as $parameter) {
            if ('entityManager' === $parameter->getName()) {
                $type = $parameter->getType();
                $this->assertInstanceOf(\ReflectionNamedType::class, $type);
                $this->assertEquals(EntityManagerInterface::class, $type->getName());
            } elseif ('classroomRepository' === $parameter->getName()) {
                $type = $parameter->getType();
                $this->assertInstanceOf(\ReflectionNamedType::class, $type);
                $this->assertEquals(ClassroomRepository::class, $type->getName());
            } elseif ('scheduleRepository' === $parameter->getName()) {
                $type = $parameter->getType();
                $this->assertInstanceOf(\ReflectionNamedType::class, $type);
                $this->assertEquals(ClassroomScheduleRepository::class, $type->getName());
            } elseif ('logger' === $parameter->getName()) {
                $type = $parameter->getType();
                $this->assertInstanceOf(\ReflectionNamedType::class, $type);
                $this->assertEquals(LoggerInterface::class, $type->getName());
            } elseif ('security' === $parameter->getName()) {
                $type = $parameter->getType();
                $this->assertInstanceOf(\ReflectionNamedType::class, $type);
                $this->assertEquals(Security::class, $type->getName());
            }
        }
    }

    public function testCreateClassroomMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'createClassroom');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('data', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $type = $parameters[0]->getType();
        $this->assertEquals('array', (string) $type);

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals(Classroom::class, $returnType->getName());
    }

    public function testUpdateClassroomMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'updateClassroom');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('data', $parameters[1]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals(Classroom::class, $returnType->getName());
    }

    public function testDeleteClassroomMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'deleteClassroom');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', (string) $returnType);
    }

    public function testGetClassroomByIdMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'getClassroomById');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('id', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $type = $parameters[0]->getType();
        $this->assertEquals('int', (string) $type);

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertTrue($returnType->allowsNull());
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals(Classroom::class, $returnType->getName());
    }

    public function testGetAvailableClassroomsMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'getAvailableClassrooms');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('type', $parameters[0]->getName());
        $this->assertEquals('minCapacity', $parameters[1]->getName());
        $this->assertEquals('filters', $parameters[2]->getName());

        // 验证参数默认值
        $this->assertTrue($parameters[0]->allowsNull());
        $this->assertTrue($parameters[1]->allowsNull());
        $this->assertTrue($parameters[2]->isDefaultValueAvailable());
        $this->assertEquals([], $parameters[2]->getDefaultValue());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testUpdateClassroomStatusMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'updateClassroomStatus');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('status', $parameters[1]->getName());
        $this->assertEquals('reason', $parameters[2]->getName());

        $this->assertTrue($parameters[2]->allowsNull());
    }

    public function testIsClassroomAvailableMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'isClassroomAvailable');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startTime', $parameters[1]->getName());
        $this->assertEquals('endTime', $parameters[2]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('bool', (string) $returnType);
    }

    public function testGetClassroomUsageStatsMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'getClassroomUsageStats');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startDate', $parameters[1]->getName());
        $this->assertEquals('endDate', $parameters[2]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testGetClassroomDevicesMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'getClassroomDevices');
        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testUpdateClassroomDevicesMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'updateClassroomDevices');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('devices', $parameters[1]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertEquals(Classroom::class, $returnType->getName());
    }

    public function testGetEnvironmentDataMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'getEnvironmentData');
        $parameters = $method->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('classroom', $parameters[0]->getName());
        $this->assertEquals('startTime', $parameters[1]->getName());
        $this->assertEquals('endTime', $parameters[2]->getName());

        $this->assertTrue($parameters[1]->allowsNull());
        $this->assertTrue($parameters[2]->allowsNull());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testBatchImportClassroomsMethod(): void
    {
        $method = new \ReflectionMethod(ClassroomService::class, 'batchImportClassrooms');
        $parameters = $method->getParameters();

        $this->assertCount(2, $parameters);
        $this->assertEquals('classroomsData', $parameters[0]->getName());
        $this->assertEquals('dryRun', $parameters[1]->getName());

        $this->assertTrue($parameters[1]->isDefaultValueAvailable());
        $this->assertFalse($parameters[1]->getDefaultValue());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('array', (string) $returnType);
    }

    public function testPrivateMethodsExist(): void
    {
        $reflection = new \ReflectionClass(ClassroomService::class);

        $this->assertTrue($reflection->hasMethod('populateClassroomData'));
        $this->assertTrue($reflection->hasMethod('setBasicClassroomData'));
        $this->assertTrue($reflection->hasMethod('setClassroomEnumData'));
        $this->assertTrue($reflection->hasMethod('setClassroomMetadata'));

        // 验证私有方法的可见性
        $populateMethod = $reflection->getMethod('populateClassroomData');
        $this->assertTrue($populateMethod->isPrivate());

        $setBasicMethod = $reflection->getMethod('setBasicClassroomData');
        $this->assertTrue($setBasicMethod->isPrivate());

        $setEnumMethod = $reflection->getMethod('setClassroomEnumData');
        $this->assertTrue($setEnumMethod->isPrivate());

        $setMetadataMethod = $reflection->getMethod('setClassroomMetadata');
        $this->assertTrue($setMetadataMethod->isPrivate());
    }

    public function testInterfaceMethodsImplemented(): void
    {
        $interfaceReflection = new \ReflectionClass(ClassroomServiceInterface::class);
        $serviceReflection = new \ReflectionClass(ClassroomService::class);

        $interfaceMethods = $interfaceReflection->getMethods();

        foreach ($interfaceMethods as $interfaceMethod) {
            $this->assertTrue(
                $serviceReflection->hasMethod($interfaceMethod->getName()),
                'Service class missing method: ' . $interfaceMethod->getName()
            );
        }
    }

    /**
     * 测试updateClassroom方法 - 更新教室信息
     */
    public function testUpdateClassroom(): void
    {
        $service = self::getService(ClassroomService::class);

        // 创建必需的关联对象
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);

        // 创建一个真实的教室实体用于测试
        $classroom = new Classroom();
        $classroom->setTitle('原始教室');
        $classroom->setCapacity(40);
        $classroom->setLocation('测试位置');
        $classroom->setType('PHYSICAL');
        $classroom->setCategory($category);
        $classroom->setCourse($course);

        // 保存到数据库以便后续更新
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($classroom);
        $entityManager->flush();

        $data = [
            'title' => '更新后的教室',
            'capacity' => 60,
        ];

        $result = $service->updateClassroom($classroom, $data);
        $this->assertInstanceOf(Classroom::class, $result);
        $this->assertSame($classroom, $result);
        $this->assertEquals('更新后的教室', $result->getTitle());
        $this->assertEquals(60, $result->getCapacity());
    }

    /**
     * 测试deleteClassroom方法 - 删除教室
     */
    public function testDeleteClassroom(): void
    {
        $service = self::getService(ClassroomService::class);

        // 创建必需的关联对象
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);

        // 创建一个真实的教室实体用于测试
        $classroom = new Classroom();
        $classroom->setTitle('待删除教室');
        $classroom->setCapacity(30);
        $classroom->setLocation('测试位置');
        $classroom->setType('PHYSICAL');
        $classroom->setCategory($category);
        $classroom->setCourse($course);

        // 保存到数据库以便后续删除
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($classroom);
        $entityManager->flush();

        $classroomId = $classroom->getId();

        $result = $service->deleteClassroom($classroom);
        $this->assertTrue($result);

        // 验证教室已被删除
        $deletedClassroom = $entityManager->find(Classroom::class, $classroomId);
        $this->assertNull($deletedClassroom);
    }

    /**
     * 测试getClassroomById方法 - 根据ID获取教室
     */
    public function testGetClassroomById(): void
    {
        $service = self::getService(ClassroomService::class);

        // 创建必需的关联对象
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);

        // 创建一个真实的教室实体用于测试
        $classroom = new Classroom();
        $classroom->setTitle('测试教室');
        $classroom->setCapacity(40);
        $classroom->setLocation('测试位置');
        $classroom->setType('PHYSICAL');
        $classroom->setCategory($category);
        $classroom->setCourse($course);

        // 保存到数据库
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($classroom);
        $entityManager->flush();

        $classroomId = (int) $classroom->getId();

        $result = $service->getClassroomById($classroomId);
        $this->assertSame($classroom, $result);
        $this->assertEquals('测试教室', $result->getTitle());
    }

    /**
     * 测试getAvailableClassrooms方法 - 获取可用教室列表
     */
    public function testGetAvailableClassrooms(): void
    {
        $service = self::getService(ClassroomService::class);

        // 创建必需的关联对象
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);

        // 创建多个教室实体用于测试
        $classroom1 = new Classroom();
        $classroom1->setTitle('大教室');
        $classroom1->setCapacity(50);
        $classroom1->setLocation('一楼');
        $classroom1->setType('PHYSICAL');
        $classroom1->setStatus('ACTIVE');
        $classroom1->setCategory($category);
        $classroom1->setCourse($course);

        $classroom2 = new Classroom();
        $classroom2->setTitle('小教室');
        $classroom2->setCapacity(20);
        $classroom2->setLocation('二楼');
        $classroom2->setType('PHYSICAL');
        $classroom2->setStatus('ACTIVE');
        $classroom2->setCategory($category);
        $classroom2->setCourse($course);

        // 保存到数据库
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($classroom1);
        $entityManager->persist($classroom2);
        $entityManager->flush();

        $result = $service->getAvailableClassrooms(null, 30);
        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result)); // 至少有一个教室满足容量要求
    }

    /**
     * 测试isClassroomAvailable方法 - 检查教室是否可用
     */
    public function testIsClassroomAvailable(): void
    {
        $service = self::getService(ClassroomService::class);

        // 创建必需的关联对象
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);

        // 创建一个真实的教室实体用于测试
        $classroom = new Classroom();
        $classroom->setTitle('可用性测试教室');
        $classroom->setCapacity(40);
        $classroom->setLocation('测试位置');
        $classroom->setType('PHYSICAL');
        $classroom->setStatus('ACTIVE');
        $classroom->setCategory($category);
        $classroom->setCourse($course);

        // 保存到数据库
        $entityManager = self::getService(EntityManagerInterface::class);
        $entityManager->persist($classroom);
        $entityManager->flush();

        $startTime = new \DateTimeImmutable('2024-01-01 09:00:00');
        $endTime = new \DateTimeImmutable('2024-01-01 10:00:00');

        $result = $service->isClassroomAvailable($classroom, $startTime, $endTime);
        $this->assertTrue($result);
    }

    /**
     * 测试batchImportClassrooms方法 - 批量导入教室
     */
    public function testBatchImportClassrooms(): void
    {
        $category = $this->createTestCategory();
        $course = $this->createTestCourse($category);
        $service = self::getService(ClassroomService::class);

        $classroomsData = [
            [
                'title' => '教室A',
                'location' => '一楼',
                'capacity' => 30,
                'type' => 'PHYSICAL',
                'category_id' => $category->getId(),
                'course_id' => $course->getId(),
            ],
            [
                'title' => '教室B',
                'location' => '二楼',
                'capacity' => 40,
                'type' => 'VIRTUAL',
                'category_id' => $category->getId(),
                'course_id' => $course->getId(),
            ],
        ];

        $result = $service->batchImportClassrooms($classroomsData);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('failed', $result);
        $this->assertArrayHasKey('errors', $result);

        // 验证导入结果
        $this->assertIsInt($result['success']);
        $this->assertGreaterThanOrEqual(1, $result['success']);
    }
}
