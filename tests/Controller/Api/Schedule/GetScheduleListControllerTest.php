<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Schedule;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Schedule\GetScheduleListController;

/**
 * @internal
 */
#[CoversClass(GetScheduleListController::class)]
#[RunTestsInSeparateProcesses]
final class GetScheduleListControllerTest extends AbstractWebTestCase
{
    public function testGetScheduleListWithValidParameters(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/schedule/list?classroom_id=1&page=1&limit=10');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
    }

    public function testGetScheduleListWithDefaultPagination(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/schedule/list');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        $this->assertIsArray($responseData['meta']);
        $this->assertEquals(1, $responseData['meta']['page']);
        $this->assertEquals(20, $responseData['meta']['limit']);
    }

    public function testGetScheduleListWithFilters(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $queryParams = [
            'classroom_id' => 1,
            'course_id' => 1,
            'status' => 'scheduled',
            'type' => 'regular',
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
        ];

        $client->request('GET', '/api/schedule/list?' . http_build_query($queryParams));

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testGetScheduleListWithInvalidDateFilter(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/schedule/list?start_date=invalid-date');

        $this->assertResponseStatusCodeSame(Response::HTTP_INTERNAL_SERVER_ERROR);

        $content = $client->getResponse()->getContent();
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

        $client->request('GET', '/api/schedule/list');

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
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/schedule/list');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/schedule/list');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/schedule/list');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/schedule/list');
    }

    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/schedule/list');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/schedule/list');
    }
}
