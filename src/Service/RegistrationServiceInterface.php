<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 报班注册服务接口
 */
#[Autoconfigure(autowire: false)]
interface RegistrationServiceInterface
{
    /**
     * 根据学员查找注册记录
     *
     * @return Registration[]
     */
    public function findUserRegistrations(UserInterface $student): array;

    /**
     * 根据课程状态查找报名记录
     *
     * @return Registration[]
     */
    public function findByStatus(OrderStatus $status, ?int $limit = null, ?int $offset = null): array;

    /**
     * 根据课程查找报名记录
     *
     * @return Registration[]
     */
    public function findByCourse(Course $course, ?int $limit = null, ?int $offset = null): array;

    /**
     * 根据培训类型查找报名记录
     *
     * @return Registration[]
     */
    public function findByTrainType(TrainType $trainType, ?int $limit = null, ?int $offset = null): array;

    /**
     * 查找已完成的报名记录
     *
     * @return Registration[]
     */
    public function findFinishedRegistrations(?int $limit = null, ?int $offset = null): array;

    /**
     * 查找未完成的报名记录
     *
     * @return Registration[]
     */
    public function findUnfinishedRegistrations(?int $limit = null, ?int $offset = null): array;

    /**
     * 根据用户和状态查找最新的报名记录
     */
    public function findLatestUserRegistrationByStatus(UserInterface $student, OrderStatus $status): ?Registration;

    /**
     * 根据ID查找注册记录
     */
    public function findById(string $id): ?Registration;

    /**
     * 保存注册记录
     */
    public function save(Registration $registration, bool $flush = true): void;

    /**
     * 查找学员在指定课程的注册记录
     */
    public function findUserCourseRegistration(UserInterface $student, string $courseId): ?Registration;

    /**
     * 检查学员是否已注册指定课程
     */
    public function isUserRegisteredForCourse(UserInterface $student, string $courseId): bool;

    /**
     * 获取学员的所有有效注册记录（未过期且已支付）
     *
     * @return Registration[]
     */
    public function getActiveUserRegistrations(UserInterface $student): array;
}
