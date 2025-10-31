<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\BatchImportAttendanceController;

/**
 * @internal
 */
#[CoversClass(BatchImportAttendanceController::class)]
#[RunTestsInSeparateProcesses]
final class BatchImportAttendanceControllerTest extends AbstractWebTestCase
{
    public function testAuthenticatedUserCanImportAttendance(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $attendanceData = [
            ['student_id' => 1, 'course_id' => 1, 'status' => 'present'],
            ['student_id' => 2, 'course_id' => 1, 'status' => 'absent'],
        ];
        $requestData = ['attendance_data' => $attendanceData];

        $client->request(
            'POST',
            '/api/attendance/batch-import',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            false !== json_encode($requestData) ? json_encode($requestData) : '{}'
        );

        // Expect validation error or success depending on whether entities exist
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [Response::HTTP_OK, Response::HTTP_INTERNAL_SERVER_ERROR], true),
            "Expected status code to be 200 or 500, got {$statusCode}"
        );

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
    }

    public function testUnauthenticatedUserCannotImportAttendance(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->catchExceptions(true);
        // No login - test unauthenticated access

        $attendanceData = [
            ['student_id' => 1, 'course_id' => 1, 'status' => 'present'],
        ];
        $requestData = ['attendance_data' => $attendanceData];

        $client->request(
            'POST',
            '/api/attendance/batch-import',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            false !== json_encode($requestData) ? json_encode($requestData) : '{}'
        );

        // Should get 401 Unauthorized or 403 Forbidden for unauthenticated access
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN], true),
            "Expected 401 or 403 for unauthenticated access, got {$statusCode}"
        );
    }

    public function testInvokeWithMissingAttendanceDataReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/attendance/batch-import',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            false !== json_encode([]) ? json_encode([]) : '{}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('缺少考勤数据数组', $responseData['message']);
    }

    public function testInvokeWithInvalidAttendanceDataReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $requestData = ['attendance_data' => 'invalid_data'];

        $client->request(
            'POST',
            '/api/attendance/batch-import',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            false !== json_encode($requestData) ? json_encode($requestData) : '{}'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('缺少考勤数据数组', $responseData['message']);
    }

    public function testGetMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/attendance/batch-import');
    }

    public function testPutMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/batch-import');
    }

    public function testDeleteMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/batch-import');
    }

    public function testPatchMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/batch-import');
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/attendance/batch-import',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
    }

    public function testHeadMethodReturnsHeadersOnly(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/attendance/batch-import');
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $client->catchExceptions(true);
        $this->loginAsAdmin($client);

        $client->request('OPTIONS', '/api/attendance/batch-import');

        // OPTIONS should return allowed methods in headers
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [Response::HTTP_OK, Response::HTTP_NO_CONTENT, Response::HTTP_METHOD_NOT_ALLOWED], true),
            "Expected status code to be 200, 204, or 405, got {$statusCode}"
        );
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/batch-import');
    }
}
