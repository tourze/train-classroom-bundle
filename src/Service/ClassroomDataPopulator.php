<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Tourze\CatalogBundle\Entity\Catalog;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Enum\ClassroomStatus;
use Tourze\TrainClassroomBundle\Enum\ClassroomType;
use Tourze\TrainClassroomBundle\Exception\InvalidArgumentException;
use Tourze\TrainCourseBundle\Entity\Course;

/**
 * 教室数据填充器
 *
 * 负责将数组数据填充到教室实体中
 */
class ClassroomDataPopulator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function populate(Classroom $classroom, array $data): void
    {
        $this->setBasicClassroomData($classroom, $data);
        $this->setClassroomEnumData($classroom, $data);
        $this->setClassroomMetadata($classroom, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setBasicClassroomData(Classroom $classroom, array $data): void
    {
        $this->setClassroomTitle($classroom, $data);
        $this->setClassroomNumericFields($classroom, $data);
        $this->setClassroomRelations($classroom, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomTitle(Classroom $classroom, array $data): void
    {
        if (isset($data['name']) && is_string($data['name'])) {
            $classroom->setTitle($data['name']);
        } elseif (isset($data['title']) && is_string($data['title'])) {
            $classroom->setTitle($data['title']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomNumericFields(Classroom $classroom, array $data): void
    {
        if (isset($data['capacity'])) {
            $capacity = is_numeric($data['capacity']) ? (int) $data['capacity'] : 0;
            $classroom->setCapacity($capacity);
        }

        if (isset($data['area'])) {
            $area = is_numeric($data['area']) ? (float) $data['area'] : 0.0;
            $classroom->setArea($area);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomRelations(Classroom $classroom, array $data): void
    {
        if (isset($data['category_id'])) {
            $categoryId = is_numeric($data['category_id']) ? (int) $data['category_id'] : 0;
            $category = $this->entityManager->find(Catalog::class, $categoryId);
            if (null === $category) {
                throw new InvalidArgumentException("分类ID {$categoryId} 不存在");
            }
            $classroom->setCategory($category);
        }

        if (isset($data['course_id'])) {
            $courseId = is_numeric($data['course_id']) ? (int) $data['course_id'] : 0;
            $course = $this->entityManager->find(Course::class, $courseId);
            if (null === $course) {
                throw new InvalidArgumentException("课程ID {$courseId} 不存在");
            }
            $classroom->setCourse($course);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomEnumData(Classroom $classroom, array $data): void
    {
        if (isset($data['type'])) {
            if (is_string($data['type'])) {
                $type = ClassroomType::from($data['type']);
                $classroom->setType($type->value);
            } elseif ($data['type'] instanceof ClassroomType) {
                $classroom->setType($data['type']->value);
            }
        }

        if (isset($data['status'])) {
            if (is_string($data['status'])) {
                $status = ClassroomStatus::from($data['status']);
                $classroom->setStatus($status->value);
            } elseif ($data['status'] instanceof ClassroomStatus) {
                $classroom->setStatus($data['status']->value);
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomMetadata(Classroom $classroom, array $data): void
    {
        $this->setClassroomTextFields($classroom, $data);
        $this->setClassroomDevices($classroom, $data);
        $this->setClassroomSupplierId($classroom, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomTextFields(Classroom $classroom, array $data): void
    {
        if (isset($data['location'])) {
            $location = is_string($data['location']) ? $data['location'] : null;
            $classroom->setLocation($location);
        }

        if (isset($data['description'])) {
            $description = is_string($data['description']) ? $data['description'] : null;
            $classroom->setDescription($description);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomDevices(Classroom $classroom, array $data): void
    {
        if (!isset($data['devices']) || !is_array($data['devices'])) {
            return;
        }

        /** @var array<string, mixed> $devices */
        $devices = [];
        foreach ($data['devices'] as $key => $value) {
            $devices[(string) $key] = $value;
        }
        $classroom->setDevices($devices);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setClassroomSupplierId(Classroom $classroom, array $data): void
    {
        if (isset($data['supplier_id'])) {
            $supplierId = is_numeric($data['supplier_id']) ? (int) $data['supplier_id'] : 0;
            $classroom->setSupplierId($supplierId);
        }
    }
}
