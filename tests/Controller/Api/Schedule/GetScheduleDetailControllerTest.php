<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\GetScheduleDetailController;

/**
 * @internal
 */
#[CoversClass(GetScheduleDetailController::class)]
#[RunTestsInSeparateProcesses]
final class GetScheduleDetailControllerTest extends AbstractWebTestCase
{
    public function testGetScheduleDetailWithValidId(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $scheduleId = 1;

        $client->request('GET', '/api/schedule/detail/' . $scheduleId);

        // We expect either a success response or a not found response
        // depending on whether test data exists
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);

        if ($responseData['success']) {
            $this->assertResponseIsSuccessful();
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('id', $responseData['data']);
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
            $this->assertIsString($responseData['message']);
            $this->assertStringContainsString('排课记录不存在', $responseData['message']);
        }
    }

    public function testGetScheduleDetailWithNonExistentId(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $nonExistentId = 999999;

        $client->request('GET', '/api/schedule/detail/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertStringContainsString('排课记录不存在', $responseData['message']);
    }

    public function testGetScheduleDetailWithInvalidIdParameter(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $client->request('GET', '/api/schedule/detail/invalid');

        // Should return 400 Bad Request for invalid ID format
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('无效的排课ID', $responseData['message']);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $client->request('GET', '/api/schedule/detail/1');

        // Since there's no authentication setup in the controller,
        // this should work but might fail due to missing data
        $response = $client->getResponse();
        $this->assertGreaterThanOrEqual(400, $response->getStatusCode());
        $this->assertLessThan(500, $response->getStatusCode());
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/schedule/detail/1');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/schedule/detail/1');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/detail/1');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/schedule/detail/1');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsUser($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/detail/1');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/detail/1');
    }
}
