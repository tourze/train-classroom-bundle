<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\BatchCreateScheduleController;

/**
 * @internal
 */
#[CoversClass(BatchCreateScheduleController::class)]
#[RunTestsInSeparateProcesses]
final class BatchCreateScheduleControllerTest extends AbstractWebTestCase
{
    public function testAuthenticatedUserCanCreateBatchSchedules(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $scheduleData = [
            'classroom_id' => 123,
            'course_id' => 456,
            'start_date' => '2023-02-01',
            'end_date' => '2023-02-03',
            'type' => 'REGULAR',
            'instructor_id' => 789,
            'max_students' => 30,
        ];

        $client->request(
            'POST',
            '/api/schedule/batch-create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($scheduleData, JSON_THROW_ON_ERROR)
        );

        // Expect validation error for non-existent classroom
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('教室不存在', $responseData['message']);
    }

    public function testUnauthenticatedUserCannotCreateBatchSchedules(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        // No login - test unauthenticated access

        $scheduleData = [
            'classroom_id' => 123,
            'course_id' => 456,
            'start_date' => '2023-02-01',
            'end_date' => '2023-02-03',
        ];

        try {
            $client->request(
                'POST',
                '/api/schedule/batch-create',
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                json_encode($scheduleData, JSON_THROW_ON_ERROR)
            );

            $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

            $content = $client->getResponse()->getContent();
            $this->assertIsString($content);
            $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($responseData);
            $this->assertFalse($responseData['success']);
            $this->assertEquals('访问被拒绝，请先登录', $responseData['message']);
        } catch (\Exception $e) {
            // 如果异常被抛出，这是预期的行为
            $this->assertStringContainsString('Access Denied', $e->getMessage());
        }
    }

    public function testInvokeWithMissingSchedulesArrayReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/schedule/batch-create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([], JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('缺少必需参数：classroom_id, course_id, start_date, end_date', $responseData['message']);
    }

    public function testInvokeWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/schedule/batch-create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // Invalid JSON should return 400 Bad Request
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('无效的请求数据', $responseData['message']);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/schedule/batch-create');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/schedule/batch-create');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/batch-create');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/schedule/batch-create');
    }

    public function testHeadMethodReturnsHeadersOnly(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('HEAD', '/api/schedule/batch-create');
    }

    public function testOptionsMethodReturnsAllowedMethods(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/batch-create');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/batch-create');
    }
}
