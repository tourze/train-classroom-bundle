<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;
use Tourze\TrainClassroomBundle\Repository\RegistrationRepository;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 报名服务
 *
 * 提供报名相关的业务逻辑处理
 */
#[WithMonologChannel(channel: 'train_classroom')]
class RegistrationService implements RegistrationServiceInterface
{
    public function __construct(
        private readonly RegistrationRepository $registrationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 根据课程状态查找报名记录
     *
     * @return Registration[]
     */
    public function findByStatus(OrderStatus $status, ?int $limit = null, ?int $offset = null): array
    {
        return $this->registrationRepository->findBy(['status' => $status], ['createTime' => 'DESC'], $limit, $offset);
    }

    /**
     * 根据课程查找报名记录
     *
     * @return Registration[]
     */
    public function findByCourse(Course $course, ?int $limit = null, ?int $offset = null): array
    {
        return $this->registrationRepository->findBy(['course' => $course], ['createTime' => 'DESC'], $limit, $offset);
    }

    /**
     * 根据培训类型查找报名记录
     *
     * @return Registration[]
     */
    public function findByTrainType(TrainType $trainType, ?int $limit = null, ?int $offset = null): array
    {
        return $this->registrationRepository->findBy(['trainType' => $trainType], ['createTime' => 'DESC'], $limit, $offset);
    }

    /**
     * 查找已完成的报名记录
     *
     * @return Registration[]
     */
    public function findFinishedRegistrations(?int $limit = null, ?int $offset = null): array
    {
        return $this->registrationRepository->findBy(['finished' => true], ['finishTime' => 'DESC'], $limit, $offset);
    }

    /**
     * 查找未完成的报名记录
     *
     * @return Registration[]
     */
    public function findUnfinishedRegistrations(?int $limit = null, ?int $offset = null): array
    {
        return $this->registrationRepository->findBy(['finished' => false], ['createTime' => 'DESC'], $limit, $offset);
    }

    /**
     * 根据用户和状态查找最新的报名记录
     */
    public function findLatestUserRegistrationByStatus(UserInterface $student, OrderStatus $status): ?Registration
    {
        return $this->registrationRepository->findOneBy(
            ['student' => $student, 'status' => $status],
            ['createTime' => 'DESC']
        );
    }

    /**
     * 根据ID查找报名记录
     */
    public function findById(string $id): ?Registration
    {
        return $this->registrationRepository->find($id);
    }

    /**
     * @return Registration[]
     */
    public function findUserRegistrations(UserInterface $student): array
    {
        return $this->registrationRepository->findBy(['student' => $student]);
    }

    /**
     * 保存报名记录
     */
    public function save(Registration $registration, bool $flush = true): void
    {
        $this->entityManager->persist($registration);

        if ($flush) {
            $this->entityManager->flush();
        }

        $this->logger->info('报名记录已保存', [
            'registration_id' => $registration->getId(),
            'student_id' => $registration->getStudent()->getUserIdentifier(),
            'course_id' => $registration->getCourse()->getId(),
        ]);
    }

    public function findUserCourseRegistration(UserInterface $student, string $courseId): ?Registration
    {
        return $this->registrationRepository->findOneBy([
            'student' => $student,
            'course' => $courseId,
        ]);
    }

    public function isUserRegisteredForCourse(UserInterface $student, string $courseId): bool
    {
        return null !== $this->findUserCourseRegistration($student, $courseId);
    }

    /**
     * @return Registration[]
     */
    public function getActiveUserRegistrations(UserInterface $student): array
    {
        return $this->registrationRepository->findBy([
            'student' => $student,
            'isActive' => true,
        ]);
    }
}
