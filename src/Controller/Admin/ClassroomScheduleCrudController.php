<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\TrainClassroomBundle\Entity\ClassroomSchedule;
use Tourze\TrainClassroomBundle\Enum\ScheduleStatus;
use Tourze\TrainClassroomBundle\Enum\ScheduleType;

/**
 * @extends AbstractCrudController<ClassroomSchedule>
 */
#[AdminCrud(routePath: '/train-classroom/schedule', routeName: 'train_classroom_schedule')]
final class ClassroomScheduleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ClassroomSchedule::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('排课')
            ->setEntityLabelInPlural('排课管理')
            ->setPageTitle(Crud::PAGE_INDEX, '排课管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建排课')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑排课')
            ->setPageTitle(Crud::PAGE_DETAIL, '排课详情')
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $scheduleTypeField = EnumField::new('scheduleType', '排课类型')
            ->setRequired(true)
            ->setHelp('课程的类型分类')
        ;
        $scheduleTypeField->setEnumCases(ScheduleType::cases());

        $scheduleStatusField = EnumField::new('scheduleStatus', '排课状态')
            ->setRequired(true)
            ->setHelp('当前排课的状态')
        ;
        $scheduleStatusField->setEnumCases(ScheduleStatus::cases());

        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('classroom', '所属教室')
                ->setRequired(true)
                ->setHelp('选择要排课的教室'),
            TextField::new('teacherId', '教师ID')
                ->setMaxLength(100)
                ->setRequired(true)
                ->setHelp('授课教师的唯一标识'),
            DateField::new('scheduleDate', '排课日期')
                ->setRequired(true)
                ->setHelp('课程安排的日期'),
            DateTimeField::new('startTime', '开始时间')
                ->setRequired(true)
                ->setHelp('课程开始的具体时间'),
            DateTimeField::new('endTime', '结束时间')
                ->setRequired(true)
                ->setHelp('课程结束的具体时间'),
            $scheduleTypeField,
            $scheduleStatusField,
            IntegerField::new('expectedStudents', '预期学员数')
                ->setHelp('预期参与该课程的学员数量')
                ->hideOnIndex(),
            IntegerField::new('actualStudents', '实际学员数')
                ->setHelp('实际参与该课程的学员数量')
                ->hideOnIndex(),
            TextareaField::new('courseContent', '课程内容')
                ->hideOnIndex()
                ->setHelp('该节课的具体教学内容'),
            CodeEditorField::new('scheduleConfig', '排课配置')
                ->setLanguage('javascript')
                ->hideOnIndex()
                ->setHelp('排课相关的配置信息（JSON格式）'),
            TextareaField::new('remark', '备注')
                ->hideOnIndex()
                ->setHelp('额外的备注信息'),
            DateTimeField::new('createTime', '创建时间')
                ->onlyOnIndex(),
            DateTimeField::new('updateTime', '更新时间')
                ->onlyOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('classroom')
            ->add('teacherId')
            ->add('scheduleDate')
            ->add('scheduleType')
            ->add('scheduleStatus')
            ->add('createTime')
        ;
    }
}
