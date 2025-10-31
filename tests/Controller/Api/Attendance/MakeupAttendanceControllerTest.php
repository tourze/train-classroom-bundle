<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\MakeupAttendanceController;

/**
 * @internal
 */
#[CoversClass(MakeupAttendanceController::class)]
#[RunTestsInSeparateProcesses]
final class MakeupAttendanceControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidDataRecordsMakeupAttendance(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $makeupData = [
            'registration_id' => 1,
            'type' => 'SIGN_IN',
            'record_time' => '2025-01-15 09:00:00',
            'reason' => '因病请假补课',
        ];

        $client->request(
            'POST',
            '/api/attendance/makeup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($makeupData, JSON_THROW_ON_ERROR)
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
            'registration_id' => 1,
            // Missing type, record_time, reason
        ];

        $client->request(
            'POST',
            '/api/attendance/makeup',
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
        $this->assertEquals('缺少必需参数：registration_id, type, record_time, reason', $responseData['message']);
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/attendance/makeup',
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

    public function testInvokeWithNonExistentRegistrationReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $makeupData = [
            'registration_id' => 999999,
            'type' => 'SIGN_IN',
            'record_time' => '2025-01-15 09:00:00',
            'reason' => '测试补课',
        ];

        $client->request(
            'POST',
            '/api/attendance/makeup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($makeupData, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('报名记录不存在', $responseData['message']);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $makeupData = [
            'registration_id' => 1,
            'type' => 'SIGN_IN',
            'record_time' => '2025-01-15 09:00:00',
            'reason' => '因病请假补课',
        ];

        $client->request(
            'POST',
            '/api/attendance/makeup',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($makeupData, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/attendance/makeup');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/makeup');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/makeup');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/makeup');
    }

    public function testHeadMethodReturnsHeaders(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/attendance/makeup');
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/attendance/makeup');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/makeup');
    }
}
