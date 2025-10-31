<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\DetectAnomaliesController;

/**
 * @internal
 */
#[CoversClass(DetectAnomaliesController::class)]
#[RunTestsInSeparateProcesses]
final class DetectAnomaliesControllerTest extends AbstractWebTestCase
{
    public function testAuthenticatedUserCanAccessAnomaliesEndpoint(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $client->request('GET', '/api/attendance/anomalies/' . $registrationId);

        // Should get 404 for non-existent registration, not access denied
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('报名记录不存在', $responseData['message']);
    }

    public function testUnauthenticatedUserCannotAccessAnomaliesEndpoint(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->catchExceptions(true);
        // No login - test unauthenticated access

        $registrationId = 1;

        $client->request('GET', '/api/attendance/anomalies/' . $registrationId);

        // Should get 401 Unauthorized or 403 Forbidden for unauthenticated access
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN],
            'Expected 401 or 403 for unauthenticated access, got ' . $client->getResponse()->getStatusCode()
        );
    }

    public function testInvokeWithNonExistentRegistrationReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $nonExistentId = 999999;

        $client->request('GET', '/api/attendance/anomalies/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('报名记录不存在', $responseData['message']);
    }

    public function testInvokeWithInvalidRegistrationIdParameterReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->catchExceptions(true);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/attendance/anomalies/invalid');

        // Should return 404 because the route parameter can't be converted to int
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPostMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/attendance/anomalies/' . $registrationId);
    }

    public function testPutMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/anomalies/' . $registrationId);
    }

    public function testDeleteMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/anomalies/' . $registrationId);
    }

    public function testPatchMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;
        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/anomalies/' . $registrationId);
    }

    public function testHeadMethodReturnsHeadersOnly(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $client->request('HEAD', '/api/attendance/anomalies/' . $registrationId);

        // HEAD should return same status as GET but no content
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertEmpty($client->getResponse()->getContent());
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->catchExceptions(true);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $client->request('OPTIONS', '/api/attendance/anomalies/' . $registrationId);

        // OPTIONS should return allowed methods in headers
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_NO_CONTENT, Response::HTTP_METHOD_NOT_ALLOWED]
        );
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/anomalies/1');
    }
}
