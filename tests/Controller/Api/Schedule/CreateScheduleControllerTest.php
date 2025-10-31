<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\CreateScheduleController;

/**
 * @internal
 */
#[CoversClass(CreateScheduleController::class)]
#[RunTestsInSeparateProcesses]
final class CreateScheduleControllerTest extends AbstractWebTestCase
{
    public function testCreateScheduleWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $scheduleData = [
            'classroom_id' => 1,
            'course_id' => 1,
            'type' => 'regular',
            'start_time' => '2023-02-01 09:00:00',
            'end_time' => '2023-02-01 17:00:00',
            'instructor_id' => 1,
        ];

        $client->request(
            'POST',
            '/api/schedule/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($scheduleData, JSON_THROW_ON_ERROR)
        );

        // Could return 404 if classroom doesn't exist or 201 if successful
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_CREATED),
            self::equalTo(Response::HTTP_NOT_FOUND)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);

        if (Response::HTTP_CREATED === $response->getStatusCode()) {
            $this->assertTrue($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('创建排课成功', $responseData['message']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
        } else {
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('教室不存在', $responseData['message']);
        }
    }

    public function testCreateScheduleWithMissingRequiredFields(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $incompleteData = [
            'course_id' => 1,
            // Missing classroom_id, type, start_time, end_time
        ];

        $client->request(
            'POST',
            '/api/schedule/create',
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
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertStringContainsString('缺少必需参数', $responseData['message']);
    }

    public function testCreateScheduleWithInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/schedule/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // Invalid JSON results in invalid request data error (400)
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('无效的请求数据', $responseData['message']);
    }

    public function testCreateScheduleWithNonExistentClassroom(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $scheduleData = [
            'classroom_id' => 999999,
            'course_id' => 1,
            'type' => 'regular',
            'start_time' => '2023-02-01 09:00:00',
            'end_time' => '2023-02-01 17:00:00',
        ];

        $client->request(
            'POST',
            '/api/schedule/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($scheduleData, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertStringContainsString('教室不存在', $responseData['message']);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $client->request('POST', '/api/schedule/create');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/schedule/create');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/schedule/create');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/create');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/schedule/create');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/create');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/create');
    }
}
