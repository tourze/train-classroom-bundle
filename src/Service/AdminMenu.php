<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Entity\Classroom;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Entity\Qrcode;
use Tourze\TrainClassroomBundle\Entity\Registration;

/**
 * 培训教室管理后台菜单提供者
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(
        private LinkGeneratorInterface $linkGenerator,
    ) {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 添加培训教室管理顶级菜单
        if (null === $item->getChild('培训教室管理')) {
            $item->addChild('培训教室管理')
                ->setAttribute('icon', 'fas fa-chalkboard-teacher')
            ;
        }

        $trainMenu = $item->getChild('培训教室管理');
        if (null === $trainMenu) {
            return;
        }

        $trainMenu->addChild('班级管理')
            ->setUri($this->linkGenerator->getCurdListPage(Classroom::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        $trainMenu->addChild('报名管理')
            ->setUri($this->linkGenerator->getCurdListPage(Registration::class))
            ->setAttribute('icon', 'fas fa-user-plus')
        ;

        $trainMenu->addChild('二维码管理')
            ->setUri($this->linkGenerator->getCurdListPage(Qrcode::class))
            ->setAttribute('icon', 'fas fa-qrcode')
        ;

        $trainMenu->addChild('排课管理')
            ->setUri($this->linkGenerator->getCurdListPage(ClassroomSchedule::class))
            ->setAttribute('icon', 'fas fa-calendar-alt')
        ;

        $trainMenu->addChild('考勤记录')
            ->setUri($this->linkGenerator->getCurdListPage(AttendanceRecord::class))
            ->setAttribute('icon', 'fas fa-clipboard-check')
        ;
    }
}
