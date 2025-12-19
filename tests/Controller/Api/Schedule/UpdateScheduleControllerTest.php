<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\UpdateScheduleController;

/**
 * @internal
 */
#[CoversClass(UpdateScheduleController::class)]
#[RunTestsInSeparateProcesses]
final class UpdateScheduleControllerTest extends AbstractWebTestCase
{
    public function testUpdateScheduleWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $updateData = [
            'start_time' => '2023-02-01 10:00:00',
            'end_time' => '2023-02-01 13:00:00',
            'teacher_id' => 789,
            'expected_students' => 35,
        ];

        $client->request(
            'PUT',
            '/api/schedule/update/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData, JSON_THROW_ON_ERROR)
        );

        // Could return 404 if schedule doesn't exist or 200 if successful
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_OK),
            self::equalTo(Response::HTTP_NOT_FOUND)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);

        if (Response::HTTP_OK === $response->getStatusCode()) {
            $this->assertTrue($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('更新排课成功', $responseData['message']);
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
        } else {
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('排课记录不存在', $responseData['message']);
        }
    }

    public function testUpdateScheduleWithPatchMethod(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $updateData = [
            'expected_students' => 40,
        ];

        $client->request(
            'PATCH',
            '/api/schedule/update/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData, JSON_THROW_ON_ERROR)
        );

        // Could return 404 if schedule doesn't exist or 200 if successful
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_OK),
            self::equalTo(Response::HTTP_NOT_FOUND)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);

        if (Response::HTTP_OK === $response->getStatusCode()) {
            $this->assertTrue($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('更新排课成功', $responseData['message']);
        } else {
            $this->assertFalse($responseData['success']);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('排课记录不存在', $responseData['message']);
        }
    }

    public function testUpdateScheduleWithNonExistentId(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $updateData = [
            'expected_students' => 30,
        ];

        $client->request(
            'PUT',
            '/api/schedule/update/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData, JSON_THROW_ON_ERROR)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertStringContainsString('排课记录不存在', $responseData['message']);
    }

    public function testUpdateScheduleWithInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'PUT',
            '/api/schedule/update/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // Could return 404 if schedule doesn't exist or 500 if JSON is invalid
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_INTERNAL_SERVER_ERROR),
            self::equalTo(Response::HTTP_NOT_FOUND)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
    }

    public function testUpdateScheduleWithInvalidDateTime(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $updateData = [
            'start_time' => 'invalid-datetime',
        ];

        $client->request(
            'PUT',
            '/api/schedule/update/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData, JSON_THROW_ON_ERROR)
        );

        // Could return 404 if schedule doesn't exist or 400 if datetime is invalid
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_BAD_REQUEST),
            self::equalTo(Response::HTTP_NOT_FOUND)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $client->request('PUT', '/api/schedule/update/1');

        // 302 redirect to login is also valid for unauthenticated access
        $statusCode = $client->getResponse()->getStatusCode();
        $this->assertTrue(
            in_array($statusCode, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN, Response::HTTP_FOUND], true)
            || ($statusCode >= 400 && $statusCode < 500),
            "Expected 302, 401, 403 or 4xx error, got {$statusCode}"
        );
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/schedule/update/1');
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/schedule/update/1');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/update/1');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/update/1');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/update/1');
    }
}
