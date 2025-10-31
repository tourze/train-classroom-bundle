<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\TrainClassroomBundle\Entity\Qrcode;

/**
 * @extends AbstractCrudController<Qrcode>
 */
#[AdminCrud(routePath: '/train-classroom/qrcode', routeName: 'train_classroom_qrcode')]
final class QrcodeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Qrcode::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('二维码')
            ->setEntityLabelInPlural('二维码管理')
            ->setPageTitle(Crud::PAGE_INDEX, '二维码管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建二维码')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑二维码')
            ->setPageTitle(Crud::PAGE_DETAIL, '二维码详情')
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            TextField::new('title', '二维码名称')
                ->setMaxLength(120)
                ->setRequired(true),
            AssociationField::new('classroom', '所属班级')
                ->setRequired(true),
            IntegerField::new('limitNumber', '限制人数')
                ->setRequired(true)
                ->setHelp('设置该二维码的报名人数限制'),
            BooleanField::new('valid', '是否有效')
                ->setHelp('设置二维码是否可用于报名'),
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
            ->add('title')
            ->add('valid')
            ->add('limitNumber')
            ->add('createTime')
        ;
    }
}
