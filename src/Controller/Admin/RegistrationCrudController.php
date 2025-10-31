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
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\TrainClassroomBundle\Entity\Registration;
use Tourze\TrainClassroomBundle\Enum\OrderStatus;
use Tourze\TrainClassroomBundle\Enum\TrainType;

/**
 * @extends AbstractCrudController<Registration>
 */
#[AdminCrud(routePath: '/train-classroom/registration', routeName: 'train_classroom_registration')]
final class RegistrationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Registration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('报名')
            ->setEntityLabelInPlural('报名管理')
            ->setPageTitle(Crud::PAGE_INDEX, '报名管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建报名')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑报名')
            ->setPageTitle(Crud::PAGE_DETAIL, '报名详情')
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $trainTypeField = EnumField::new('trainType', '培训类型')
            ->setHelp('培训的具体类型')
        ;
        $trainTypeField->setEnumCases(TrainType::cases());

        $statusField = EnumField::new('status', '订单状态')
            ->setRequired(true)
            ->setHelp('报名订单的当前状态')
        ;
        $statusField->setEnumCases(OrderStatus::cases());

        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('classroom', '所属班级')
                ->setRequired(true)
                ->setHelp('学员报名的班级'),
            AssociationField::new('student', '学员')
                ->setRequired(true)
                ->setHelp('报名的学员用户'),
            AssociationField::new('course', '关联课程')
                ->setRequired(true)
                ->setHelp('报名的课程'),
            $trainTypeField,
            $statusField,
            DateTimeField::new('beginTime', '开通时间')
                ->setRequired(true)
                ->setHelp('报名生效的开始时间'),
            DateTimeField::new('endTime', '过期时间')
                ->setHelp('报名有效期结束时间'),
            DateTimeField::new('firstLearnTime', '首次学习时间')
                ->hideOnIndex()
                ->setHelp('学员首次参与学习的时间'),
            DateTimeField::new('lastLearnTime', '最后学习时间')
                ->hideOnIndex()
                ->setHelp('学员最后一次参与学习的时间'),
            AssociationField::new('qrcode', '关联二维码')
                ->setHelp('通过哪个二维码报名的'),
            DateTimeField::new('payTime', '支付时间')
                ->hideOnIndex()
                ->setHelp('完成支付的时间'),
            DateTimeField::new('refundTime', '退款时间')
                ->hideOnIndex()
                ->setHelp('申请退款的时间'),
            MoneyField::new('payPrice', '扣款金额')
                ->setCurrency('CNY')
                ->hideOnIndex()
                ->setHelp('实际支付的金额'),
            BooleanField::new('finished', '是否完成')
                ->setHelp('学员是否已完成该课程学习'),
            DateTimeField::new('finishTime', '完成时间')
                ->hideOnIndex()
                ->setHelp('课程完成的时间'),
            BooleanField::new('expired', '是否已过期')
                ->hideOnIndex()
                ->setHelp('报名是否已过期'),
            IntegerField::new('age', '报名年龄')
                ->hideOnIndex()
                ->setHelp('学员报名时的年龄'),
            TextField::new('createdFromUa', '创建UA')
                ->hideOnIndex()
                ->setMaxLength(2000),
            TextField::new('updatedFromUa', '更新UA')
                ->hideOnIndex()
                ->setMaxLength(2000),
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
            ->add('student')
            ->add('course')
            ->add('status')
            ->add('trainType')
            ->add('beginTime')
            ->add('createTime')
        ;
    }
}
