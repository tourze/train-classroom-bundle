<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Param\GetJobTrainingJoinedClassroomListParam;
use Tourze\TrainClassroomBundle\Procedure\GetJobTrainingJoinedClassroomList;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(GetJobTrainingJoinedClassroomList::class)]
#[RunTestsInSeparateProcesses]
final class GetJobTrainingJoinedClassroomListTest extends AbstractProcedureTestCase
{
    protected function onSetUp(): void
    {
        // 这里不需要调用 parent::setUp()，AbstractProcedureTestCase 会正确处理
    }

    public function testProcedureCanBeInstantiated(): void
    {
        $procedure = self::getService(GetJobTrainingJoinedClassroomList::class);
        $this->assertInstanceOf(GetJobTrainingJoinedClassroomList::class, $procedure);
    }

    public function testProcedureExtendsBaseProcedure(): void
    {
        $reflection = new \ReflectionClass(GetJobTrainingJoinedClassroomList::class);
        $this->assertTrue($reflection->isSubclassOf(BaseProcedure::class));
    }

    public function testConstructorParameters(): void
    {
        $reflection = new \ReflectionClass(GetJobTrainingJoinedClassroomList::class);
        $constructor = $reflection->getConstructor();

        $this->assertNotNull($constructor);
        $parameters = $constructor->getParameters();
        $this->assertCount(2, $parameters);

        $this->assertEquals('security', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $type = $parameters[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals('Symfony\Bundle\SecurityBundle\Security', $type->getName());

        $this->assertEquals('registrationRepository', $parameters[1]->getName());
        $this->assertTrue($parameters[1]->hasType());
        $type = $parameters[1]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $type);
        $this->assertEquals('Tourze\TrainClassroomBundle\Repository\RegistrationRepository', $type->getName());
    }

    public function testExecuteMethod(): void
    {
        $method = new \ReflectionMethod(GetJobTrainingJoinedClassroomList::class, 'execute');

        $this->assertTrue($method->isPublic());

        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);

        $firstParam = $parameters[0];
        $this->assertEquals('param', $firstParam->getName());
        $this->assertTrue($firstParam->hasType());
        $paramType = $firstParam->getType();
        $this->assertInstanceOf(\ReflectionUnionType::class, $paramType);

        $paramTypes = $paramType->getTypes();
        $this->assertCount(2, $paramTypes);
        $this->assertEquals(GetJobTrainingJoinedClassroomListParam::class, $paramTypes[0]->getName());
        $this->assertEquals('Tourze\JsonRPC\Core\Contracts\RpcParamInterface', $paramTypes[1]->getName());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Tourze\JsonRPC\Core\Result\ArrayResult', (string) $returnType);
    }

    public function testProcedureAttributes(): void
    {
        $reflection = new \ReflectionClass(GetJobTrainingJoinedClassroomList::class);

        // 测试 MethodDoc 属性
        $methodDocAttributes = $reflection->getAttributes(MethodDoc::class);
        $this->assertCount(1, $methodDocAttributes);

        $methodDoc = $methodDocAttributes[0]->newInstance();
        $this->assertEquals('获取当前学员的班级信息', $methodDoc->summary);

        // 测试 MethodExpose 属性
        $methodExposeAttributes = $reflection->getAttributes(MethodExpose::class);
        $this->assertCount(1, $methodExposeAttributes);

        $methodExpose = $methodExposeAttributes[0]->newInstance();
        $this->assertEquals('GetJobTrainingJoinedClassroomList', $methodExpose->method);

        // 测试 MethodTag 属性
        $methodTagAttributes = $reflection->getAttributes(MethodTag::class);
        $this->assertCount(1, $methodTagAttributes);

        $methodTag = $methodTagAttributes[0]->newInstance();
        $this->assertEquals('培训教室', $methodTag->name);

        // 测试 IsGranted 属性
        $isGrantedAttributes = $reflection->getAttributes(IsGranted::class);
        $this->assertCount(1, $isGrantedAttributes);

        $isGranted = $isGrantedAttributes[0]->newInstance();
        $this->assertEquals('IS_AUTHENTICATED_FULLY', $isGranted->attribute);

        // 测试 Autoconfigure 属性
        $autoconfigureAttributes = $reflection->getAttributes(Autoconfigure::class);
        $this->assertCount(1, $autoconfigureAttributes);

        $autoconfigure = $autoconfigureAttributes[0]->newInstance();
        $this->assertTrue($autoconfigure->public);
    }

    public function testExecuteWithAuthenticatedUser(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('test@example.com', 'password123');

        // 手动设置认证用户
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        self::assertInstanceOf(TokenStorageInterface::class, $tokenStorage);
        $tokenStorage->setToken($token);

        // 创建测试课程分类
        $entityManager = self::getEntityManager();

        $catalogType = new CatalogType();
        $catalogType->setCode('training_course');
        $catalogType->setName('培训课程');
        $entityManager->persist($catalogType);
        $entityManager->flush();

        $catalog = new Catalog();
        $catalog->setName('测试分类');
        $catalog->setType($catalogType);
        $entityManager->persist($catalog);
        $entityManager->flush();

        // 创建测试课程
        $course = new Course();
        $course->setTitle('测试课程');
        $course->setCategory($catalog);
        $course->setLearnHour(10);
        $entityManager->persist($course);
        $entityManager->flush();

        // 创建测试班级
        $classroom = new Classroom();
        $classroom->setTitle('测试班级');
        $classroom->setCategory($catalog);
        $classroom->setCourse($course);
        $classroom->setStartTime(new \DateTimeImmutable());
        $classroom->setEndTime(new \DateTimeImmutable('+30 days'));
        $entityManager->persist($classroom);
        $entityManager->flush();

        // 创建报名记录
        $registration = new Registration();
        $registration->setStudent($user);
        $registration->setClassroom($classroom);
        $entityManager->persist($registration);
        $entityManager->flush();

        // 执行 Procedure
        $procedure = self::getService(GetJobTrainingJoinedClassroomList::class);
        $param = new GetJobTrainingJoinedClassroomListParam();
        $result = $procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->jsonSerialize();
        $this->assertArrayHasKey('list', $resultArray);
        $this->assertIsArray($resultArray['list']);
        $this->assertCount(1, $resultArray['list']);

        $firstItem = $resultArray['list'][0];
        $this->assertIsArray($firstItem);
        $this->assertEquals($registration->getId(), $firstItem['id']);
        $this->assertArrayHasKey('classroom', $firstItem);
        $this->assertIsArray($firstItem['classroom']);
        $this->assertEquals($classroom->getTitle(), $firstItem['classroom']['title']);
    }

    public function testExecuteWithUnauthenticatedUser(): void
    {
        // 不登录用户

        $procedure = self::getService(GetJobTrainingJoinedClassroomList::class);
        $param = new GetJobTrainingJoinedClassroomListParam();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('请先登录');
        $this->expectExceptionCode(-885);

        $procedure->execute($param);
    }

    public function testExecuteWithEmptyRegistrations(): void
    {
        // 创建测试用户但没有报名记录
        $user = $this->createNormalUser('test2@example.com', 'password123');

        // 手动设置认证用户
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        self::assertInstanceOf(TokenStorageInterface::class, $tokenStorage);
        $tokenStorage->setToken($token);

        $procedure = self::getService(GetJobTrainingJoinedClassroomList::class);
        $param = new GetJobTrainingJoinedClassroomListParam();
        $result = $procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->jsonSerialize();
        $this->assertArrayHasKey('list', $resultArray);
        $this->assertIsArray($resultArray['list']);
        $this->assertCount(0, $resultArray['list']);
    }

    public function testExecuteWithMultipleRegistrations(): void
    {
        // 创建测试用户
        $user = $this->createNormalUser('test3@example.com', 'password123');

        // 手动设置认证用户
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $tokenStorage = self::getService(TokenStorageInterface::class);
        self::assertInstanceOf(TokenStorageInterface::class, $tokenStorage);
        $tokenStorage->setToken($token);

        $entityManager = self::getEntityManager();

        // 创建测试课程分类
        $catalogType = new CatalogType();
        $catalogType->setCode('training_course_multi');
        $catalogType->setName('培训课程');
        $entityManager->persist($catalogType);
        $entityManager->flush();

        $catalog = new Catalog();
        $catalog->setName('测试分类');
        $catalog->setType($catalogType);
        $entityManager->persist($catalog);
        $entityManager->flush();

        // 创建多个课程和班级
        for ($i = 1; $i <= 2; ++$i) {
            $course = new Course();
            $course->setTitle("测试课程{$i}");
            $course->setCategory($catalog);
            $course->setLearnHour(10);
            $entityManager->persist($course);
            $entityManager->flush();

            $classroom = new Classroom();
            $classroom->setTitle("测试班级{$i}");
            $classroom->setCategory($catalog);
            $classroom->setCourse($course);
            $classroom->setStartTime(new \DateTimeImmutable());
            $classroom->setEndTime(new \DateTimeImmutable('+30 days'));
            $entityManager->persist($classroom);
            $entityManager->flush();

            // 创建报名记录
            $registration = new Registration();
            $registration->setStudent($user);
            $registration->setClassroom($classroom);
            $entityManager->persist($registration);
        }
        $entityManager->flush();

        $procedure = self::getService(GetJobTrainingJoinedClassroomList::class);
        $param = new GetJobTrainingJoinedClassroomListParam();
        $result = $procedure->execute($param);

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Result\ArrayResult::class, $result);
        $resultArray = $result->jsonSerialize();
        $this->assertArrayHasKey('list', $resultArray);
        $this->assertIsArray($resultArray['list']);
        $this->assertCount(2, $resultArray['list']);
    }

    public function testProcedureNamespace(): void
    {
        $reflection = new \ReflectionClass(GetJobTrainingJoinedClassroomList::class);
        $this->assertEquals('Tourze\TrainClassroomBundle\Procedure', $reflection->getNamespaceName());
    }

    public function testProcedureErrorHandling(): void
    {
        // 验证 procedure 有正确的异常处理机制
        $reflection = new \ReflectionClass(GetJobTrainingJoinedClassroomList::class);
        $method = $reflection->getMethod('execute');

        // 验证方法有正确的返回类型声明
        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);
        $this->assertEquals('Tourze\JsonRPC\Core\Result\ArrayResult', (string) $returnType);

        // 验证类使用了正确的异常处理属性
        $attributes = $reflection->getAttributes();
        $hasSecurity = false;
        foreach ($attributes as $attribute) {
            if (IsGranted::class === $attribute->getName()) {
                $hasSecurity = true;
                break;
            }
        }
        $this->assertTrue($hasSecurity, 'Procedure should have security attribute for error handling');
    }
}
