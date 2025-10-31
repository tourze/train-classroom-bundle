<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\TrainClassroomBundle\Entity\Classroom;

/**
 * @extends AbstractCrudController<Classroom>
 */
#[AdminCrud(routePath: '/train-classroom/classroom', routeName: 'train_classroom_classroom')]
final class ClassroomCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Classroom::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('班级')
            ->setEntityLabelInPlural('班级管理')
            ->setPageTitle(Crud::PAGE_INDEX, '班级管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建班级')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑班级')
            ->setPageTitle(Crud::PAGE_DETAIL, '班级详情')
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('title', '班级名称')
                ->setMaxLength(150)
                ->setRequired(true)
                ->setHelp('班级的显示名称'),
            AssociationField::new('category', '所属分类')
                ->setRequired(true)
                ->setHelp('班级所属的分类'),
            AssociationField::new('course', '关联课程')
                ->setRequired(true)
                ->setHelp('班级对应的课程'),
            DateTimeField::new('startTime', '开始时间')
                ->setHelp('班级的开始时间'),
            DateTimeField::new('endTime', '结束时间')
                ->setHelp('班级的结束时间'),
            ChoiceField::new('type', '教室类型')
                ->setChoices([
                    '实体教室' => 'PHYSICAL',
                    '虚拟教室' => 'VIRTUAL',
                    '混合教室' => 'HYBRID',
                ])
                ->setHelp('教室的使用类型'),
            ChoiceField::new('status', '教室状态')
                ->setChoices([
                    '活跃' => 'ACTIVE',
                    '停用' => 'INACTIVE',
                    '维护中' => 'MAINTENANCE',
                    '已预订' => 'RESERVED',
                ])
                ->setHelp('教室当前的状态'),
            IntegerField::new('capacity', '容量')
                ->setHelp('教室的最大容纳人数')
                ->hideOnIndex(),
            NumberField::new('area', '面积（平方米）')
                ->setNumDecimals(2)
                ->hideOnIndex()
                ->setHelp('教室的使用面积'),
            TextField::new('location', '位置')
                ->setMaxLength(255)
                ->hideOnIndex()
                ->setHelp('教室的物理位置'),
            TextareaField::new('description', '描述')
                ->hideOnIndex()
                ->setHelp('教室的详细描述'),
            CodeEditorField::new('devices', '设备信息')
                ->setLanguage('javascript')
                ->hideOnIndex()
                ->setHelp('教室设备配置信息（JSON格式）'),
            IntegerField::new('supplierId', '供应商ID')
                ->hideOnIndex()
                ->setHelp('教室设备供应商ID'),
            TextField::new('createdBy', '创建者')
                ->setMaxLength(100)
                ->hideOnIndex(),
            TextField::new('updatedBy', '更新者')
                ->setMaxLength(100)
                ->hideOnIndex(),
            DateTimeField::new('createTime', '创建时间')
                ->onlyOnIndex(),
            DateTimeField::new('updateTime', '更新时间')
                ->onlyOnIndex(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('category')
            ->add('title')
            ->add('type')
            ->add('status')
            ->add('course')
            ->add('createTime')
        ;
    }
}
