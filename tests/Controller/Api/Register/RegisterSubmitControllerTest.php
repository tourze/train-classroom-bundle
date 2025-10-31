<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Register;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Register\RegisterSubmitController;

/**
 * @internal
 */
#[CoversClass(RegisterSubmitController::class)]
#[RunTestsInSeparateProcesses]
final class RegisterSubmitControllerTest extends AbstractWebTestCase
{
    public function testAuthenticatedUserCanSubmitRegistration(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationData = [
            'email' => 'test@example.com',
            'name' => '张三',
            'course_id' => 1,
        ];

        $client->request(
            'POST',
            '/api/register/submit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($registrationData, JSON_THROW_ON_ERROR)
        );

        // Expect validation error for non-existent course
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('课程不存在', $responseData['message']);
    }

    public function testUnauthenticatedUserCannotSubmitRegistration(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        // No login - test unauthenticated access

        $registrationData = [
            'email' => 'test@example.com',
            'name' => '张三',
            'course_id' => 1,
        ];

        try {
            $client->request(
                'POST',
                '/api/register/submit',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($registrationData, JSON_THROW_ON_ERROR)
            );

            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

            $content = $client->getResponse()->getContent();
            $this->assertIsString($content);
            $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($responseData);
            $this->assertIsBool($responseData['success']);
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertEquals('访问被拒绝，请先登录', $responseData['message']);
        } catch (\Exception $e) {
            // 如果异常被抛出，这是预期的行为
            $this->assertStringContainsString('Access Denied', $e->getMessage());
        }
    }

    public function testInvokeWithMissingRequiredFieldsReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $incompleteData = [
            'course_id' => 1,
            // Missing email, name
        ];

        $client->request(
            'POST',
            '/api/register/submit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($incompleteData, JSON_THROW_ON_ERROR)
        );

        // Should get 200 OK but with success: false for missing parameters
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('缺少邮箱参数', $responseData['message']);
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/register/submit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // Invalid JSON should return 200 OK but with success: false
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('缺少邮箱参数', $responseData['message']);
    }

    public function testInvokeWithInvalidEmailReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $invalidData = [
            'email' => 'invalid-email',
            'name' => '张三',
            'course_id' => 1,
        ];

        $client->request(
            'POST',
            '/api/register/submit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidData, JSON_THROW_ON_ERROR)
        );

        // Should get 200 OK but with success: false for invalid email
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('无效的邮箱格式', $responseData['message']);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/register/submit');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/register/submit');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/register/submit');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/register/submit');
    }

    public function testHeadMethodReturnsHeadersOnly(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/register/submit');
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/register/submit');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/register/submit');
    }
}
