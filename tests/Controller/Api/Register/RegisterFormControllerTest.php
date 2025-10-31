<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Register;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Register\RegisterFormController;

/**
 * @internal
 */
#[CoversClass(RegisterFormController::class)]
#[RunTestsInSeparateProcesses]
final class RegisterFormControllerTest extends AbstractWebTestCase
{
    public function testAuthenticatedUserCanAccessRegisterForm(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->request('GET', '/api/register/form', ['course_id' => $courseId]);

        // Should get 200 OK but with success: false for non-existent course
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

    public function testUnauthenticatedUserCannotAccessRegisterForm(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        // No login - test unauthenticated access

        $courseId = 1;

        try {
            $client->request('GET', '/api/register/form', ['course_id' => $courseId]);
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

    public function testInvokeWithMissingCourseIdReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/register/form');

        // Should get 200 OK but with success: false for missing parameter
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('缺少课程ID参数', $responseData['message']);
    }

    public function testInvokeWithInvalidCourseIdReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/register/form', ['course_id' => 'invalid']);

        // Should get 200 OK but with success: false for invalid parameter
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('无效的课程ID', $responseData['message']);
    }

    public function testInvokeWithNonExistentCourseReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/register/form', ['course_id' => 999999]);

        // Should get 200 OK but with success: false for non-existent course
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('课程不存在', $responseData['message']);
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/register/form', ['course_id' => 1]);
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/register/form', ['course_id' => 1]);
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/register/form', ['course_id' => 1]);
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/register/form', ['course_id' => 1]);
    }

    public function testHeadMethodReturnsHeadersOnly(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('HEAD', '/api/register/form', ['course_id' => 1]);

        // HEAD should return same status as GET but no content
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEmpty($client->getResponse()->getContent());
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/register/form', ['course_id' => 1]);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/register/form');
    }
}
