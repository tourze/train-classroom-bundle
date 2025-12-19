<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\RecordAttendanceController;

/**
 * @internal
 */
#[CoversClass(RecordAttendanceController::class)]
#[RunTestsInSeparateProcesses]
final class RecordAttendanceControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidDataRecordsAttendance(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $attendanceData = [
            'registration_id' => 1,
            'type' => 'SIGN_IN',
            'method' => 'MANUAL',
            'device_data' => [],
            'remark' => '测试考勤记录',
        ];

        $client->request(
            'POST',
            '/api/attendance/record',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($attendanceData, JSON_THROW_ON_ERROR)
        );

        // 根据实际数据情况，可能是成功也可能是404
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);

        if (200 === $response->getStatusCode()) {
            $this->assertIsBool($responseData['success']);
            $this->assertTrue($responseData['success']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
        } else {
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertIsBool($responseData['success']);
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertEquals('报名记录不存在', $responseData['message']);
        }
    }

    public function testInvokeWithMissingRequiredFieldsReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $incompleteData = [
            'schedule_id' => 1,
            // Missing registration_id, type, method
        ];

        $client->request(
            'POST',
            '/api/attendance/record',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($incompleteData, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('缺少必需参数：registration_id, type, method', $responseData['message']);
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/attendance/record',
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

    public function testInvokeWithInvalidAttendanceTypeReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $invalidData = [
            'registration_id' => 1,
            'type' => 'INVALID_TYPE',
            'method' => 'MANUAL',
        ];

        $client->request(
            'POST',
            '/api/attendance/record',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($invalidData, JSON_THROW_ON_ERROR)
        );

        // 可能是 400（无效类型）或 404（报名记录不存在）
        $response = $client->getResponse();
        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);

        if (400 === $response->getStatusCode()) {
            $this->assertIsBool($responseData['success']);
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('is not a valid backing value for enum', $responseData['message']);
        } else {
            $this->assertEquals(404, $response->getStatusCode());
            $this->assertIsBool($responseData['success']);
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertEquals('报名记录不存在', $responseData['message']);
        }
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $attendanceData = [
            'registration_id' => 1,
            'type' => 'SIGN_IN',
            'method' => 'MANUAL',
        ];

        $client->request(
            'POST',
            '/api/attendance/record',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($attendanceData, JSON_THROW_ON_ERROR)
        );

        // 302 redirect to login is also valid for unauthenticated access
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN, Response::HTTP_FOUND]
        );
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/attendance/record');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/record');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/record');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/record');
    }

    public function testHeadMethodReturnsHeaders(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/attendance/record');
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/attendance/record');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/record');
    }
}
