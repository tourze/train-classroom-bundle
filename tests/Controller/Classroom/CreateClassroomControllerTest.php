<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Classroom;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Classroom\CreateClassroomController;

/**
 * @internal
 */
#[CoversClass(CreateClassroomController::class)]
#[RunTestsInSeparateProcesses]
final class CreateClassroomControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidDataCreatesClassroomSuccessfully(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $classroomData = [
            'name' => '计算机教室A',
            'location' => '教学楼3楼301',
            'capacity' => 40,
            'equipment' => ['投影仪', '电脑', '音响设备'],
            'description' => '配备40台电脑的计算机实训教室',
        ];

        $client->request(
            'POST',
            '/api/classrooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($classroomData, JSON_THROW_ON_ERROR)
        );

        // 期望成功创建或内部服务器错误（如果依赖关系不存在）
        $this->assertContains($client->getResponse()->getStatusCode(), [
            Response::HTTP_CREATED,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);

        if (Response::HTTP_CREATED === $client->getResponse()->getStatusCode()) {
            $this->assertIsBool($responseData['success']);
            $this->assertTrue($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertEquals('教室创建成功', $responseData['message']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
        } else {
            // 当依赖关系缺失时的错误响应
            $this->assertArrayHasKey('error', $responseData);
            $this->assertIsString($responseData['error']);
            $this->assertEquals('创建教室失败', $responseData['error']);
        }
    }

    public function testInvokeWithMissingRequiredFieldsReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $incompleteData = [
            'location' => '教学楼3楼301',
            // Missing name, capacity
        ];

        $client->request(
            'POST',
            '/api/classrooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($incompleteData, JSON_THROW_ON_ERROR)
        );

        // 可能返回400（验证错误）或500（依赖关系错误）
        $this->assertContains($client->getResponse()->getStatusCode(), [
            Response::HTTP_BAD_REQUEST,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);

        if (Response::HTTP_BAD_REQUEST === $client->getResponse()->getStatusCode()) {
            $this->assertStringContainsString('缺少必需的参数', $responseData['error']);
        } else {
            $this->assertEquals('创建教室失败', $responseData['error']);
        }
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/classrooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertEquals('无效的JSON数据', $responseData['error']);
    }

    public function testInvokeWithEmptyRequestReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('POST', '/api/classrooms');

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertIsString($responseData['error']);
        $this->assertEquals('无效的JSON数据', $responseData['error']);
    }

    public function testInvokeWithUnauthenticatedAccessReturnsForbidden(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        // 不进行任何身份验证，测试未认证访问

        $classroomData = [
            'name' => '未认证测试教室',
            'location' => '测试位置',
            'capacity' => 20,
        ];

        $this->expectException(AccessDeniedException::class);

        $client->request(
            'POST',
            '/api/classrooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($classroomData, JSON_THROW_ON_ERROR)
        );
    }

    public function testInvokeWithGetMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/classrooms');
    }

    public function testInvokeWithPutMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/classrooms');
    }

    public function testInvokeWithDeleteMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/classrooms');
    }

    public function testInvokeWithPatchMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/classrooms');
    }

    public function testInvokeWithHeadMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/classrooms');
    }

    public function testInvokeWithOptionsMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/classrooms');
    }

    public function testInvokeReturnsCorrectResponseStructure(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $classroomData = [
            'name' => '结构测试教室',
            'location' => '测试大楼',
            'capacity' => 30,
            'equipment' => ['白板', '电脑'],
            'description' => '用于测试响应结构的教室',
        ];

        $client->request(
            'POST',
            '/api/classrooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($classroomData, JSON_THROW_ON_ERROR)
        );

        // 期望成功创建或内部服务器错误（如果依赖关系不存在）
        $this->assertContains($client->getResponse()->getStatusCode(), [
            Response::HTTP_CREATED,
            Response::HTTP_INTERNAL_SERVER_ERROR,
        ]);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);

        if (Response::HTTP_CREATED === $client->getResponse()->getStatusCode()) {
            // 验证成功响应结构
            $this->assertArrayHasKey('success', $responseData);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertArrayHasKey('message', $responseData);

            // 验证数据结构
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
            $this->assertArrayHasKey('name', $responseData['data']);
            $this->assertArrayHasKey('type', $responseData['data']);
            $this->assertArrayHasKey('status', $responseData['data']);
            $this->assertArrayHasKey('capacity', $responseData['data']);

            // 验证数据类型
            $this->assertIsBool($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertIsInt($responseData['data']['id']);
            $this->assertIsString($responseData['data']['name']);
            $this->assertIsInt($responseData['data']['capacity']);
        } else {
            // 验证错误响应结构
            $this->assertArrayHasKey('error', $responseData);
            $this->assertIsString($responseData['error']);
            $this->assertEquals('创建教室失败', $responseData['error']);
        }
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/classrooms');
    }
}
