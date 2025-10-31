# TrainClassroomBundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-classroom-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-classroom-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/train-classroom-bundle/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/train-classroom-bundle/actions)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/train-classroom-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/train-classroom-bundle/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/train-classroom-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-classroom-bundle)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-%3E%3D6.4-000000.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

[English](README.md) | [中文](README.zh-CN.md)

培训教室管理Bundle，为安全生产培训系统提供完整的教室管理、考勤管理和排课管理功能。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [配置选项](#配置选项)
- [使用指南](#使用指南)
- [命令行工具](#命令行工具)
- [API 参考](#api-参考)
- [数据模型](#数据模型)
- [Advanced Usage](#advanced-usage)
- [开发指南](#开发指南)
- [测试](#测试)
- [许可证](#许可证)

## 功能特性

### 🎯 核心功能

- **考勤管理** - 多种考勤方式支持（人脸识别、刷卡、指纹、二维码等）
- **排课管理** - 智能排课、冲突检测、资源调度
- **教室管理** - 物理/虚拟教室、设施配置、环境监控
- **数据统计** - 考勤率统计、使用率分析、异常检测
- **档案管理** - "一期一档"、培训记录、视频存档

### 🏗️ 技术特性

- **DDD架构** - 领域驱动设计，职责清晰
- **RESTful API** - 完整的REST API接口
- **多租户支持** - 通过supplierId支持多租户
- **审计追踪** - 完整的操作审计记录
- **配置灵活** - 丰富的配置选项
- **命令行工具** - 数据同步、清理等维护工具
- **事件驱动** - 基于事件的可扩展架构
- **PHP 8.1+** - 使用现代PHP特性和严格类型
- **Symfony 6.4+** - 基于稳定的Symfony组件构建

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- Doctrine ORM 3.0 或更高版本
- MySQL 5.7+ 或 PostgreSQL 10+
- Bundle 依赖：
  - `tourze/doctrine-snowflake-bundle` - 用于ID生成
  - `tourze/doctrine-timestamp-bundle` - 用于时间戳管理
  - `tourze/doctrine-indexed-bundle` - 用于索引管理
  - `tourze/idcard-manage-bundle` - 用于身份证验证
  - `tourze/train-course-bundle` - 用于课程管理集成
  - `tourze/train-category-bundle` - 用于分类管理

## 安装

### 1. 通过Composer安装

```bash
composer require tourze/train-classroom-bundle
```

### 2. 注册Bundle

在 `config/bundles.php` 中添加：

```php
return [
    // ...
    Tourze\TrainClassroomBundle\TrainClassroomBundle::class => ['all' => true],
];
```

### 3. 配置Bundle

本Bundle使用环境变量和服务配置而不是YAML配置文件。所有配置都通过服务参数和环境变量处理。

在 `config/services.yaml` 中配置服务：

```yaml
services:
  # 导入bundle服务
  _instanceof:
    Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface:
      tags: ['train_classroom.attendance_service']
    Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface:
      tags: ['train_classroom.schedule_service']
```

在 `.env` 文件中设置环境变量：

```bash
# 考勤设置
ATTENDANCE_FACE_RECOGNITION_ENABLED=true
ATTENDANCE_CARD_READER_ENABLED=true
ATTENDANCE_QR_CODE_ENABLED=true
ATTENDANCE_SIGN_IN_TOLERANCE_MINUTES=15
ATTENDANCE_SIGN_OUT_TOLERANCE_MINUTES=15

# 排课设置
SCHEDULE_DEFAULT_DURATION_MINUTES=120
SCHEDULE_MIN_BREAK_MINUTES=15
SCHEDULE_MAX_ADVANCE_BOOKING_DAYS=90

# 归档设置
ARCHIVE_ATTENDANCE_RETENTION_DAYS=1095
ARCHIVE_VIDEO_RETENTION_DAYS=365
```

### 4. 创建数据库表

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## 配置选项

此Bundle使用环境变量进行配置。请在 `.env` 文件中设置以下变量：

### 考勤配置

```bash
# 启用/禁用考勤方式
ATTENDANCE_FACE_RECOGNITION_ENABLED=true
ATTENDANCE_FINGERPRINT_ENABLED=false
ATTENDANCE_CARD_READER_ENABLED=true
ATTENDANCE_QR_CODE_ENABLED=true

# 容错设置（分钟）
ATTENDANCE_SIGN_IN_TOLERANCE_MINUTES=15
ATTENDANCE_SIGN_OUT_TOLERANCE_MINUTES=15

# 允许补签
ATTENDANCE_ALLOW_MAKEUP=true
```

### 排课配置

```bash
# 排课默认设置
SCHEDULE_DEFAULT_DURATION_MINUTES=120
SCHEDULE_MIN_BREAK_MINUTES=15
SCHEDULE_ALLOW_OVERLAPPING=false
SCHEDULE_MAX_ADVANCE_BOOKING_DAYS=90
```

### 教室配置

```bash
# 监控设置
CLASSROOM_ENABLE_MONITORING=true
CLASSROOM_ENABLE_ENVIRONMENT_MONITORING=false
```

### 存档配置

```bash
# 数据保留设置（天）
ARCHIVE_ATTENDANCE_RETENTION_DAYS=1095  # 3年
ARCHIVE_VIDEO_RETENTION_DAYS=365        # 1年
ARCHIVE_ENABLE_AUTO_CLEANUP=true
```

## 使用指南

### 考勤管理

#### 记录考勤

```bash
curl -X POST /api/attendance/record \
  -H "Content-Type: application/json" \
  -d '{
    "registration_id": 123,
    "type": "sign_in",
    "method": "face",
    "device_data": {
      "device_id": "face_001",
      "confidence": 0.95
    },
    "remark": "正常签到"
  }'
```

#### 获取考勤统计

```bash
curl /api/attendance/statistics/123
```

#### 检测考勤异常

```bash
curl /api/attendance/anomalies/123?date=2025-05-27
```

#### 补录考勤

```bash
curl -X POST /api/attendance/makeup \
  -H "Content-Type: application/json" \
  -d '{
    "registration_id": 123,
    "type": "sign_in",
    "record_time": "2025-05-27 09:00:00",
    "reason": "设备故障，手动补录"
  }'
```

### 排课管理

#### 创建排课

```bash
curl -X POST /api/schedule/create \
  -H "Content-Type: application/json" \
  -d '{
    "classroom_id": 1,
    "course_id": 100,
    "type": "regular",
    "start_time": "2025-05-28 09:00:00",
    "end_time": "2025-05-28 11:00:00",
    "options": {
      "title": "安全生产培训",
      "instructor_id": 5,
      "max_participants": 30
    }
  }'
```

#### 检测排课冲突

```bash
curl -X POST /api/schedule/conflicts \
  -H "Content-Type: application/json" \
  -d '{
    "classroom_id": 1,
    "start_time": "2025-05-28 09:00:00",
    "end_time": "2025-05-28 11:00:00"
  }'
```

#### 查找可用教室

```bash
curl "/api/schedule/available-classrooms?start_time=2025-05-28 09:00:00&end_time=2025-05-28 11:00:00&min_capacity=20"
```

#### 获取排课日历

```bash
curl "/api/schedule/calendar?start_date=2025-05-01&end_date=2025-05-31&classroom_ids=1,2,3"
```

## 命令行工具

### 同步考勤数据

从CSV文件导入：
```bash
php bin/console train-classroom:sync-attendance file --file=/path/to/attendance.csv
```

从API接口同步：
```bash
php bin/console train-classroom:sync-attendance api --api-url=https://device.example.com/api/attendance
```

试运行模式：
```bash
php bin/console train-classroom:sync-attendance file --file=/path/to/data.csv --dry-run
```

### 清理过期数据

使用默认配置：
```bash
php bin/console train-classroom:cleanup-data
```

自定义保留天数：
```bash
php bin/console train-classroom:cleanup-data --attendance-days=365 --video-days=180
```

试运行模式：
```bash
php bin/console train-classroom:cleanup-data --dry-run
```

### 更新排课状态

自动更新排课状态（定时任务推荐）：
```bash
php bin/console train-classroom:update-schedule-status
```

试运行模式：
```bash
php bin/console train-classroom:update-schedule-status --dry-run
```

设置批处理大小：
```bash
php bin/console train-classroom:update-schedule-status --batch-size=50
```

### 过期报名记录处理

自动标记过期的报名记录（定时任务）：
```bash
php bin/console job-training:expire-registration
```

> 注意：此命令会自动每分钟执行一次，无需手动运行。

## 数据模型

### 核心实体

#### AttendanceRecord (考勤记录)
- `id` - 主键（雪花ID）
- `registration` - 关联报名记录
- `type` - 考勤类型（签到、签退、休息外出、休息返回）
- `method` - 考勤方式（人脸、刷卡、指纹、二维码、手动、移动端）
- `recordTime` - 记录时间
- `verificationResult` - 验证结果
- `deviceData` - 设备数据（JSON）
- `remark` - 备注

#### ClassroomSchedule (教室排课)
- `id` - 主键（雪花ID）
- `classroom` - 关联教室
- `courseId` - 课程ID
- `type` - 排课类型（常规、补课、考试、会议、实训、讲座）
- `status` - 排课状态（已排课、进行中、已完成、已取消、已暂停、已延期）
- `startTime` - 开始时间
- `endTime` - 结束时间
- `title` - 标题
- `instructorId` - 讲师ID
- `maxParticipants` - 最大参与人数

#### Classroom (教室)
- `id` - 主键（雪花ID）
- `name` - 教室名称
- `type` - 教室类型（物理、虚拟、混合）
- `status` - 教室状态（活跃、非活跃、维护中、已归档）
- `capacity` - 最大容量
- `location` - 物理位置
- `features` - 可用设施（JSON）
- `supplierId` - 租户ID

#### Registration (报名记录)
- `id` - 主键（雪花ID）
- `userId` - 用户ID
- `courseId` - 课程ID
- `classroomId` - 教室ID
- `status` - 报名状态
- `learnStatus` - 学习状态
- `registrationTime` - 报名时间

### 枚举类型

- `AttendanceType` - 考勤类型（SIGN_IN、SIGN_OUT、BREAK_OUT、BREAK_RETURN）
- `AttendanceMethod` - 考勤方式（FACE、CARD、FINGERPRINT、QR_CODE、MANUAL、MOBILE）
- `VerificationResult` - 验证结果（SUCCESS、FAILURE、PENDING、ERROR）
- `ClassroomType` - 教室类型（PHYSICAL、VIRTUAL、HYBRID）
- `ClassroomStatus` - 教室状态（ACTIVE、INACTIVE、MAINTENANCE、ARCHIVED）
- `ScheduleType` - 排课类型（REGULAR、MAKEUP、EXAM、MEETING、TRAINING、LECTURE）
- `ScheduleStatus` - 排课状态（SCHEDULED、IN_PROGRESS、COMPLETED、CANCELLED、PAUSED、POSTPONED）
- `RegistrationLearnStatus` - 学习状态（NOT_STARTED、IN_PROGRESS、COMPLETED、FAILED）

## API 参考

### AttendanceServiceInterface

- `recordAttendance()` - 记录考勤
- `batchImportAttendance()` - 批量导入考勤
- `getAttendanceStatistics()` - 获取考勤统计
- `getCourseAttendanceSummary()` - 获取课程考勤汇总
- `detectAttendanceAnomalies()` - 检测考勤异常
- `makeUpAttendance()` - 补录考勤
- `validateAttendance()` - 验证考勤有效性
- `getAttendanceRateStatistics()` - 获取考勤率统计

### ScheduleServiceInterface

- `createSchedule()` - 创建排课
- `detectScheduleConflicts()` - 检测排课冲突
- `updateScheduleStatus()` - 更新排课状态
- `getClassroomUtilizationRate()` - 获取教室使用率
- `findAvailableClassrooms()` - 查找可用教室
- `batchCreateSchedules()` - 批量创建排课
- `cancelSchedule()` - 取消排课
- `postponeSchedule()` - 延期排课
- `getScheduleCalendar()` - 获取排课日历
- `getScheduleStatisticsReport()` - 获取排课统计报表

### ClassroomServiceInterface

- `createClassroom()` - 创建教室
- `updateClassroom()` - 更新教室详情
- `getClassroomById()` - 根据ID获取教室
- `findClassrooms()` - 根据条件查找教室
- `setClassroomStatus()` - 设置教室状态
- `getClassroomFeatures()` - 获取教室设施

## Advanced Usage

### 自定义考勤方式

您可以通过扩展基础考勤服务来实现自定义考勤方式：

```php
<?php

namespace App\Service;

use Tourze\TrainClassroomBundle\Service\AttendanceService;

class CustomAttendanceService extends AttendanceService
{
    public function recordCustomAttendance(Registration $registration, array $customData): bool
    {
        // 自定义考勤逻辑
        return parent::recordAttendance($registration, $type, $method, $customData);
    }
}
```

### 高级排课功能

对于复杂的排课场景，可以使用排课服务的自定义过滤器：

```php
$criteria = [
    'start_time' => new \DateTime('2024-01-01 09:00:00'),
    'end_time' => new \DateTime('2024-01-01 17:00:00'),
    'classroom_type' => 'PHYSICAL',
    'min_capacity' => 20,
];

$availableClassrooms = $scheduleService->findAvailableClassrooms($criteria);
```

## 开发指南

### 扩展服务

如果需要扩展考勤服务，可以创建自定义服务类：

```php
<?php

namespace App\Service;

use Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface;
use Tourze\TrainClassroomBundle\Service\AttendanceService;

class CustomAttendanceService extends AttendanceService
{
    public function recordAttendance(
        Registration $registration,
        AttendanceType $type,
        AttendanceMethod $method,
        array $deviceData = [],
        ?string $remark = null
    ): AttendanceRecord {
        // 自定义逻辑
        
        return parent::recordAttendance($registration, $type, $method, $deviceData, $remark);
    }
}
```

然后在服务配置中替换默认服务：

```yaml
services:
  Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface:
    alias: App\Service\CustomAttendanceService
```

### 自定义考勤设备集成

实现设备接口来集成自定义考勤设备：

```php
<?php

namespace App\Device;

interface AttendanceDeviceInterface
{
    public function getAttendanceRecords(\DateTimeInterface $since): array;
    public function syncAttendanceRecord(AttendanceRecord $record): bool;
}
```

### 事件系统

本Bundle会派发以下几个事件供您监听：

- `AttendanceRecordedEvent` - 记录考勤时派发
- `ScheduleCreatedEvent` - 创建排课时派发
- `ScheduleUpdatedEvent` - 更新排课时派发
- `ClassroomStatusChangedEvent` - 教室状态变更时派发

事件订阅者示例：

```php
<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\TrainClassroomBundle\Event\AttendanceRecordedEvent;

class AttendanceSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AttendanceRecordedEvent::class => 'onAttendanceRecorded',
        ];
    }

    public function onAttendanceRecorded(AttendanceRecordedEvent $event): void
    {
        $record = $event->getAttendanceRecord();
        // 自定义逻辑
    }
}
```

## 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/phpunit packages/train-classroom-bundle/tests

# 运行特定测试类
./vendor/bin/phpunit packages/train-classroom-bundle/tests/Service/AttendanceServiceTest.php

# 运行覆盖率测试
./vendor/bin/phpunit packages/train-classroom-bundle/tests --coverage-html=coverage
```

运行静态分析：

```bash
# PHPStan 分析
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/train-classroom-bundle
```

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个Bundle。

## 支持

如有问题，请通过以下方式联系：

- 提交Issue: [GitHub Issues](https://github.com/tourze/train-classroom-bundle/issues)
- 邮件: support@tourze.com
