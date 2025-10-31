<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

class ClassroomScheduleFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('zh_CN');
    }

    public function load(ObjectManager $manager): void
    {
        $classroom = $this->getReference(ClassroomFixtures::CLASSROOM_REFERENCE, Classroom::class);

        // 为测试教室创建5个排课记录，确保时间不冲突
        $scheduleCount = 5;
        $baseDate = new \DateTime('2024-01-01');

        for ($j = 1; $j <= $scheduleCount; ++$j) {
            $schedule = new ClassroomSchedule();

            $schedule->setClassroom($classroom);
            $schedule->setTeacherId('teacher_' . $this->faker->numberBetween(1001, 1099));

            // 为每个排课分配不同的日期和时间，确保不冲突
            $dayOffset = ($j - 1) * 2; // 每隔2天安排一次课程
            $scheduleDate = (clone $baseDate)->modify('+' . $dayOffset . ' days');
            $schedule->setScheduleDate(\DateTimeImmutable::createFromMutable($scheduleDate));

            // 每次课程安排在不同的时间段，避免冲突
            $startHour = 9 + ($j - 1) * 2; // 9:00, 11:00, 13:00, 15:00, 17:00
            $startMinute = 0;
            $duration = 90; // 固定90分钟，避免时间重叠

            $startTime = (clone $scheduleDate)->setTime($startHour, $startMinute);
            $endTime = (clone $startTime)->modify('+' . $duration . ' minutes');

            $schedule->setStartTime(\DateTimeImmutable::createFromMutable($startTime));
            $schedule->setEndTime(\DateTimeImmutable::createFromMutable($endTime));

            /** @var ScheduleType $scheduleType */
            $scheduleType = $this->faker->randomElement(ScheduleType::cases());
            $schedule->setScheduleType($scheduleType);

            /** @var ScheduleStatus $scheduleStatus */
            $scheduleStatus = $this->faker->randomElement(ScheduleStatus::cases());
            $schedule->setScheduleStatus($scheduleStatus);

            // 生成排课配置
            $scheduleConfig = [
                'recording_enabled' => $this->faker->boolean(70),
                'live_streaming' => $this->faker->boolean(40),
                'attendance_required' => $this->faker->boolean(90),
                'break_time' => $this->faker->numberBetween(10, 30),
            ];
            $schedule->setScheduleConfig($scheduleConfig);

            /** @var string $courseContent */
            $courseContent = $this->faker->randomElement([
                'PHP基础语法讲解',
                'Symfony框架核心概念',
                '数据库设计原理',
                '前端开发实践',
                '项目实战演练',
                '代码规范与最佳实践',
                '测试驱动开发',
                '性能优化技巧',
            ]);
            $schedule->setCourseContent($courseContent);

            $expectedStudents = $this->faker->numberBetween(15, 50);
            $actualStudents = $this->faker->numberBetween(0, $expectedStudents + 5);

            $schedule->setExpectedStudents($expectedStudents);
            $schedule->setActualStudents($actualStudents);
            $schedule->setRemark($this->faker->optional(0.3)->sentence());

            $manager->persist($schedule);
            $this->addReference('schedule_test_' . $j, $schedule);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ClassroomFixtures::class];
    }
}
