# TrainClassroomBundle

培训教室管理Bundle，为安全生产培训系统提供完整的教室管理、考勤管理和排课管理功能。

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

创建配置文件 `config/packages/train_classroom.yaml`：

```yaml
train_classroom:
  attendance:
    enable_face_recognition: true
    enable_fingerprint: false
    enable_card_reader: true
    enable_qr_code: true
    sign_in_tolerance_minutes: 15
    sign_out_tolerance_minutes: 15
    allow_makeup_attendance: true
  
  schedule:
    default_schedule_duration_minutes: 120
    min_break_between_schedules_minutes: 15
    allow_overlapping_schedules: false
    max_advance_booking_days: 90
  
  classroom:
    enable_monitoring: true
    enable_environment_monitoring: false
    required_features: ['projector', 'whiteboard']
  
  notification:
    enable_email_notifications: true
    enable_sms_notifications: false
    enable_wechat_notifications: true
  
  archive:
    attendance_retention_days: 1095  # 3年
    video_retention_days: 365        # 1年
    enable_auto_cleanup: true
```

### 4. 创建数据库表

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
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

### 命令行工具

#### 同步考勤数据

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

#### 清理过期数据

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

## 数据模型

### 核心实体

#### AttendanceRecord (考勤记录)
- `id` - 主键
- `registration` - 关联报名记录
- `type` - 考勤类型（签到、签退、休息外出、休息返回）
- `method` - 考勤方式（人脸、刷卡、指纹、二维码、手动、移动端）
- `recordTime` - 记录时间
- `verificationResult` - 验证结果
- `deviceData` - 设备数据（JSON）
- `remark` - 备注

#### ClassroomSchedule (教室排课)
- `id` - 主键
- `classroom` - 关联教室
- `courseId` - 课程ID
- `type` - 排课类型（常规、补课、考试、会议、实训、讲座）
- `status` - 排课状态（已排课、进行中、已完成、已取消、已暂停、已延期）
- `startTime` - 开始时间
- `endTime` - 结束时间
- `title` - 标题
- `instructorId` - 讲师ID
- `maxParticipants` - 最大参与人数

### 枚举类型

- `AttendanceType` - 考勤类型
- `AttendanceMethod` - 考勤方式
- `VerificationResult` - 验证结果
- `ClassroomType` - 教室类型
- `ClassroomStatus` - 教室状态
- `ScheduleType` - 排课类型
- `ScheduleStatus` - 排课状态

## 服务接口

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

## 配置选项

### 考勤配置 (attendance)

- `enable_face_recognition` - 是否启用人脸识别考勤
- `enable_fingerprint` - 是否启用指纹考勤
- `enable_card_reader` - 是否启用刷卡考勤
- `enable_qr_code` - 是否启用二维码考勤
- `sign_in_tolerance_minutes` - 签到容忍时间（分钟）
- `sign_out_tolerance_minutes` - 签退容忍时间（分钟）
- `allow_makeup_attendance` - 是否允许补录考勤

### 排课配置 (schedule)

- `default_schedule_duration_minutes` - 默认排课时长（分钟）
- `min_break_between_schedules_minutes` - 排课间最小间隔（分钟）
- `allow_overlapping_schedules` - 是否允许重叠排课
- `max_advance_booking_days` - 最大提前预约天数

### 教室配置 (classroom)

- `enable_monitoring` - 是否启用教室监控
- `enable_environment_monitoring` - 是否启用环境监控
- `required_features` - 教室必需设施

### 通知配置 (notification)

- `enable_email_notifications` - 是否启用邮件通知
- `enable_sms_notifications` - 是否启用短信通知
- `enable_wechat_notifications` - 是否启用微信通知

### 归档配置 (archive)

- `attendance_retention_days` - 考勤记录保留天数
- `video_retention_days` - 视频记录保留天数
- `enable_auto_cleanup` - 是否启用自动清理

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

## 许可证

MIT License

## 贡献

欢迎提交Issue和Pull Request来改进这个Bundle。

## 支持

如有问题，请通过以下方式联系：

- 提交Issue: [GitHub Issues](https://github.com/tourze/train-classroom-bundle/issues)
- 邮件: support@example.com
