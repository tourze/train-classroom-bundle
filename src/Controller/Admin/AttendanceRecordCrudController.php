<?php

declare(strict_types=1);

namespace Tourze\TrainClassroomBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Tourze\EasyAdminEnumFieldBundle\Field\EnumField;
use Tourze\TrainClassroomBundle\Entity\AttendanceRecord;
use Tourze\TrainClassroomBundle\Enum\AttendanceMethod;
use Tourze\TrainClassroomBundle\Enum\AttendanceType;
use Tourze\TrainClassroomBundle\Enum\VerificationResult;

/**
 * @extends AbstractCrudController<AttendanceRecord>
 */
#[AdminCrud(routePath: '/train-classroom/attendance-record', routeName: 'train_classroom_attendance_record')]
final class AttendanceRecordCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AttendanceRecord::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('考勤记录')
            ->setEntityLabelInPlural('考勤记录管理')
            ->setPageTitle(Crud::PAGE_INDEX, '考勤记录管理')
            ->setPageTitle(Crud::PAGE_NEW, '创建考勤记录')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑考勤记录')
            ->setPageTitle(Crud::PAGE_DETAIL, '考勤记录详情')
            ->setPaginatorPageSize(20)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $attendanceTypeField = EnumField::new('attendanceType', '考勤类型')
            ->setRequired(true)
            ->setHelp('签到或签退')
        ;
        $attendanceTypeField->setEnumCases(AttendanceType::cases());

        $attendanceMethodField = EnumField::new('attendanceMethod', '考勤方式')
            ->setRequired(true)
            ->setHelp('二维码、人脸识别、指纹等')
        ;
        $attendanceMethodField->setEnumCases(AttendanceMethod::cases());

        $verificationResultField = EnumField::new('verificationResult', '验证结果')
            ->setRequired(true)
            ->setHelp('考勤验证的结果状态')
        ;
        $verificationResultField->setEnumCases(VerificationResult::cases());

        return [
            IdField::new('id', 'ID')->onlyOnIndex(),
            AssociationField::new('registration', '报名记录')
                ->setRequired(true)
                ->setHelp('关联的学员报名记录'),
            $attendanceTypeField,
            DateTimeField::new('attendanceTime', '考勤时间')
                ->setRequired(true)
                ->setHelp('实际考勤发生的时间'),
            $attendanceMethodField,
            BooleanField::new('isValid', '是否有效')
                ->setHelp('该考勤记录是否有效'),
            $verificationResultField,
            TextField::new('deviceId', '设备ID')
                ->setMaxLength(100)
                ->hideOnIndex(),
            TextField::new('deviceLocation', '设备位置')
                ->setMaxLength(200)
                ->hideOnIndex(),
            NumberField::new('latitude', '纬度')
                ->setNumDecimals(8)
                ->hideOnIndex(),
            NumberField::new('longitude', '经度')
                ->setNumDecimals(8)
                ->hideOnIndex(),
            CodeEditorField::new('attendanceData', '考勤数据')
                ->setLanguage('javascript')
                ->hideOnIndex()
                ->setHelp('存储考勤相关的额外数据（JSON格式）'),
            TextareaField::new('remark', '备注')
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
            ->add('registration')
            ->add('attendanceType')
            ->add('attendanceTime')
            ->add('attendanceMethod')
            ->add('isValid')
            ->add('verificationResult')
            ->add('createTime')
        ;
    }
}
