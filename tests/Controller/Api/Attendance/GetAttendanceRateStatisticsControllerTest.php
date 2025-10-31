<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceRateStatisticsController;

/**
 * @internal
 */
#[CoversClass(GetAttendanceRateStatisticsController::class)]
#[RunTestsInSeparateProcesses]
final class GetAttendanceRateStatisticsControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidCourseIdReturnsRateStatistics(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->request('GET', '/api/attendance/rate-statistics/' . $courseId);

        // We expect either a success response or a not found response
        // depending on whether test data exists
        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertIsBool($responseData['success']);

        if ($responseData['success']) {
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            // 验证数据结构是统计数组，包含考勤统计信息
            if ([] !== $responseData['data']) {
                $firstRecord = $responseData['data'][0];
                $this->assertIsArray($firstRecord);
                $this->assertArrayHasKey('registration_id', $firstRecord);
                $this->assertArrayHasKey('total_records', $firstRecord);
                $this->assertArrayHasKey('attendance_rate', $firstRecord);
            }
        }
    }

    public function testInvokeWithNonExistentCourseReturnsEmptyData(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $nonExistentId = 999999;

        $client->request('GET', '/api/attendance/rate-statistics/' . $nonExistentId);

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertEmpty($responseData['data']);
    }

    public function testInvokeWithQueryParametersHandled(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->request('GET', '/api/attendance/rate-statistics/' . $courseId, [
            'start_date' => '2023-01-01',
            'end_date' => '2023-12-31',
        ]);

        // The response should handle query parameters appropriately
        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
    }

    public function testUnauthenticatedAccessReturnsUnauthorized(): void
    {
        $client = self::createClient();
        self::getClient($client);

        $courseId = 1;

        $client->request('GET', '/api/attendance/rate-statistics/' . $courseId);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/attendance/rate-statistics/' . $courseId);
    }

    public function testPutMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->catchExceptions(false);
        $client->request('PUT', '/api/attendance/rate-statistics/' . $courseId);
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->catchExceptions(false);
        $client->request('DELETE', '/api/attendance/rate-statistics/' . $courseId);
    }

    public function testPatchMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->catchExceptions(false);
        $client->request('PATCH', '/api/attendance/rate-statistics/' . $courseId);
    }

    public function testHeadMethodReturnsHeaders(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->request('HEAD', '/api/attendance/rate-statistics/' . $courseId);

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($client->getResponse()->getContent());
    }

    public function testOptionsMethodReturnsMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedHttpException::class);

        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $courseId = 1;

        $client->catchExceptions(false);
        $client->request('OPTIONS', '/api/attendance/rate-statistics/' . $courseId);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/rate-statistics/1');
    }
}
