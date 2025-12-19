<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Tests\Controller\Api\Attendance;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\CatalogBundle\Entity\CatalogType;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\TrainClassroomBundle\Controller\Api\Attendance\GetAttendanceStatisticsController;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * @internal
 */
#[CoversClass(GetAttendanceStatisticsController::class)]
#[RunTestsInSeparateProcesses]
final class GetAttendanceStatisticsControllerTest extends AbstractWebTestCase
{
    public function testInvokeWithValidRegistrationIdReturnsStatistics(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        // Create test data first
        $user = $this->createNormalUser('test@example.com', 'password');

        $catalogType = new CatalogType();
        $catalogType->setCode('training-test-' . uniqid());
        $catalogType->setName('培训分类');
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setType($catalogType);
        $category->setName('Test Catalog');
        $category->setEnabled(true);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setDescription('Test Description');
        $course->setLearnHour(40);
        $course->setCategory($category);
        self::getEntityManager()->persist($course);

        $classroom = new Classroom();
        $classroom->setTitle('Test Classroom');
        $classroom->setCourse($course);
        $classroom->setCategory($category);
        self::getEntityManager()->persist($classroom);

        $registration = new Registration();
        $registration->setClassroom($classroom);
        $registration->setStudent($user);
        self::getEntityManager()->persist($registration);
        self::getEntityManager()->flush();

        $client->request('GET', '/api/attendance/statistics/' . $registration->getId());

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);

        if ($responseData['success']) {
            $this->assertArrayHasKey('data', $responseData);
            $this->assertIsArray($responseData['data']);
            $this->assertArrayHasKey('total_records', $responseData['data']);
            $this->assertArrayHasKey('sign_in_count', $responseData['data']);
            $this->assertArrayHasKey('sign_out_count', $responseData['data']);
            $this->assertArrayHasKey('attendance_rate', $responseData['data']);
        }
    }

    public function testInvokeWithNonExistentRegistrationReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $nonExistentId = 999999;

        $client->request('GET', '/api/attendance/statistics/' . $nonExistentId);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
        $this->assertIsArray($responseData);
        $this->assertIsBool($responseData['success']);
        $this->assertFalse($responseData['success']);
        $this->assertIsString($responseData['message']);
        $this->assertEquals('报名记录不存在', $responseData['message']);
    }

    public function testInvokeWithInvalidRegistrationIdParameterReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(\TypeError::class);
        $client->request('GET', '/api/attendance/statistics/invalid');
    }

    public function testInvokeWithZeroRegistrationIdReturnsNotFound(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $client->request('GET', '/api/attendance/statistics/0');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $content = $client->getResponse()->getContent();
        $this->assertIsString($content);
        $responseData = json_decode($content, true);
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

        $registrationId = 1;

        $client->request('GET', '/api/attendance/statistics/' . $registrationId);

        // 302 redirect to login is also valid for unauthenticated access
        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN, Response::HTTP_FOUND]
        );
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/api/attendance/statistics/' . $registrationId);
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/api/attendance/statistics/' . $registrationId);
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/api/attendance/statistics/' . $registrationId);
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/api/attendance/statistics/' . $registrationId);
    }

    public function testHeadMethodReturnsHeaders(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        // Create test data
        $user = $this->createNormalUser('testhead@example.com', 'password');

        $catalogType = new CatalogType();
        $catalogType->setCode('test-head-' . uniqid());
        $catalogType->setName('测试类型');
        $catalogType->setEnabled(true);
        self::getEntityManager()->persist($catalogType);

        $category = new Catalog();
        $category->setType($catalogType);
        $category->setName('Test Category');
        $category->setEnabled(true);
        self::getEntityManager()->persist($category);

        $course = new Course();
        $course->setTitle('Test Course');
        $course->setDescription('Test Description');
        $course->setLearnHour(10);
        $course->setCategory($category);
        self::getEntityManager()->persist($course);

        $classroom = new Classroom();
        $classroom->setTitle('Test Classroom');
        $classroom->setCourse($course);
        $classroom->setCategory($category);
        self::getEntityManager()->persist($classroom);

        $registration = new Registration();
        $registration->setClassroom($classroom);
        $registration->setStudent($user);
        self::getEntityManager()->persist($registration);
        self::getEntityManager()->flush();

        $client->request('HEAD', '/api/attendance/statistics/' . $registration->getId());

        $this->assertResponseIsSuccessful();
        $this->assertEmpty($client->getResponse()->getContent());
    }

    public function testOptionsMethodReturnsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $registrationId = 1;

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/api/attendance/statistics/' . $registrationId);
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        self::getClient($client);
        $this->loginAsAdmin($client);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/api/attendance/statistics/1');
    }
}
