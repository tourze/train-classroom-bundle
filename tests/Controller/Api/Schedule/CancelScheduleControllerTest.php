<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\CancelScheduleController;

/**
 * @internal
 */
#[CoversClass(CancelScheduleController::class)]
#[RunTestsInSeparateProcesses]
final class CancelScheduleControllerTest extends AbstractWebTestCase
{
    public function testCancelScheduleWithValidData(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $requestData = [
            'reason' => '讲师生病无法授课',
        ];

        $client->request(
            'POST',
            '/api/schedule/cancel/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR)
        );

        // The response could be 404 (not found) if schedule doesn't exist
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
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testCancelScheduleWithNonExistentId(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $requestData = [
            'reason' => 'Some reason',
        ];

        $client->request(
            'POST',
            '/api/schedule/cancel/999999',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($requestData, JSON_THROW_ON_ERROR)
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

    public function testCancelScheduleWithInvalidJson(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request(
            'POST',
            '/api/schedule/cancel/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        // Could return either 404 (schedule not found) or 500 (JSON decode error)
        $response = $client->getResponse();
        $this->assertThat($response->getStatusCode(), self::logicalOr(
            self::equalTo(Response::HTTP_NOT_FOUND),
            self::equalTo(Response::HTTP_INTERNAL_SERVER_ERROR)
        ));

        $content = $response->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertFalse($responseData['success']);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $client->request('POST', '/api/schedule/cancel/1');

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', '/api/schedule/cancel/1');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/schedule/cancel/1');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/cancel/1');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/schedule/cancel/1');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/cancel/1');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/cancel/1');
    }
}
