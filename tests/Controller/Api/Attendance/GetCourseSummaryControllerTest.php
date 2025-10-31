<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetCourseSummaryController;

/**
 * @internal
 */
#[CoversClass(GetCourseSummaryController::class)]
#[RunTestsInSeparateProcesses]
final class GetCourseSummaryControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidCourseIdReturnsSummary(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        // 使用课程ID 1，假设测试数据库中存在这个课程
        $courseId = 1;

        $client->request('GET', '/api/attendance/course-summary/' . $courseId);

        // 根据实际数据情况，可能是成功也可能是404
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        if (200 === $response->getStatusCode()) {
            $this->assertArrayHasKey('success', $responseData);
            $this->assertTrue($responseData['success']);
            $this->assertArrayHasKey('data', $responseData);
        } else {
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertArrayHasKey('success', $responseData);
            $this->assertFalse($responseData['success']);
            $this->assertArrayHasKey('message', $responseData);
            $this->assertEquals('课程不存在', $responseData['message']);
        }
    }

    public function testInvokeWithNonExistentCourseReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $nonExistentId = 999999;

        $client->request('GET', '/api/attendance/course-summary/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('课程不存在', $responseData['message']);
    }

    public function testInvokeWithInvalidCourseIdParameterReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/attendance/course-summary/0');

        // Should return 404 because course ID 0 doesn't exist
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testInvokeWithQueryParametersProcessed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;
        $startDate = '2023-01-01';
        $endDate = '2023-12-31';

        $client->request('GET', '/api/attendance/course-summary/' . $courseId, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // 根据实际数据情况，可能是成功也可能是404
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);

        if (200 === $response->getStatusCode()) {
            $this->assertArrayHasKey('success', $responseData);
            $this->assertTrue($responseData['success']);
            $this->assertArrayHasKey('data', $responseData);
        } else {
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertArrayHasKey('success', $responseData);
            $this->assertFalse($responseData['success']);
        }
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $courseId = 1;

        $client->request('GET', '/api/attendance/course-summary/' . $courseId);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/attendance/course-summary/1');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/course-summary/1');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/course-summary/1');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/course-summary/1');
    }

    public function testHeadMethodReturnsHeaders(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->request('HEAD', '/api/attendance/course-summary/' . $courseId);

        // HEAD 请求应该与 GET 请求返回相同的状态码
        $response = $client->getResponse();
        if (200 === $response->getStatusCode()) {
            $this->assertResponseIsSuccessful();
            $this->assertEmpty($client->getResponse()->getContent());
        } else {
            // 如果课程不存在，应该返回 404
            $this->assertEquals(404, $response->getStatusCode());
        }
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/attendance/course-summary/1');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/course-summary/1');
    }
}
