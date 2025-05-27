# train-classroom-bundle 开发计划

## 1. 功能描述

培训教室管理包，负责安全生产培训的班级和教室管理功能。包括班级创建、学员报名、课堂监控、考勤管理等功能。支持线上线下混合培训模式，实现培训过程的全面管理和监控。

## 2. 完整能力要求

### 2.1 现有能力

- ✅ 班级信息管理（Classroom）- 支持班级基本信息、时间安排、课程关联、题库关联
- ✅ 学员报名管理（Registration）- 支持学员报班、状态管理、学习记录关联
- ✅ 二维码管理（Qrcode）- 支持报班二维码生成和管理
- ✅ 培训分类关联 - 与ExamBundle的Category集成
- ✅ 课程关联 - 与Course实体集成
- ✅ 题库关联 - 与ExamBundle的Bank集成
- ✅ 学习会话关联 - 与LearnSession集成
- ✅ 人脸检测关联 - 与FaceDetect集成
- ✅ 时间戳和用户追踪
- ✅ EasyAdmin管理界面
- ✅ 供应商多租户支持

### 2.2 需要增强的能力

#### 2.2.1 符合AQ8011-2023要求的教室管理

- [ ] 线下培训场地管理
- [ ] 培训场地面积和容量管理
- [ ] 培训设施配置管理
- [ ] 消防安全设备管理
- [ ] 应急疏散设备管理
- [ ] 培训环境监控

#### 2.2.2 考勤设备集成

- [ ] 考勤设备配置管理
- [ ] 实时考勤数据采集
- [ ] 考勤异常检测
- [ ] 考勤报表生成
- [ ] 多种考勤方式支持（刷卡、人脸、指纹）

#### 2.2.3 监控设备管理

- [ ] 监控摄像头配置
- [ ] 实时视频监控
- [ ] 录像存储管理
- [ ] 监控异常告警
- [ ] 监控数据回放

#### 2.2.4 虚拟教室增强

- [ ] 在线教室创建和配置
- [ ] 多媒体设备管理
- [ ] 网络设备状态监控
- [ ] 虚拟教室容量管理
- [ ] 教室使用统计

#### 2.2.5 教学互动功能

- [ ] 实时互动工具
- [ ] 在线答疑系统
- [ ] 课堂讨论管理
- [ ] 学员举手发言
- [ ] 教学资料共享

#### 2.2.6 班级管理增强

- [ ] 班级排课管理
- [ ] 教师分配管理
- [ ] 学员分组管理
- [ ] 班级进度跟踪
- [ ] 班级成绩统计

## 3. 现有实体设计分析

### 3.1 现有实体

#### Classroom（班级）

- **字段**: id, supplier, category, title, startTime, endTime, course, bank
- **关联**: registrations, qrcodes
- **特性**: 支持分类关联、课程关联、题库关联、时间戳、用户追踪、供应商多租户

#### Registration（报班记录）

- **字段**: id, supplier, classroom, student, course, bank, trainType, status, beginTime, endTime, firstLearnTime, lastLearnTime, finished, finishTime, expired, age, payTime, refundTime, payPrice
- **关联**: faceDetects, sessions, learnLogs, qrcode
- **特性**: 支持学员关联、学习状态管理、支付管理、IP/UA追踪、完整的学习生命周期

#### Qrcode（二维码）

- **字段**: 基本二维码信息
- **关联**: classroom
- **特性**: 支持报班二维码生成

### 3.2 需要新增的实体

#### AttendanceRecord（考勤记录）

```php
class AttendanceRecord
{
    private string $id;
    private Registration $registration;
    private string $attendanceType;  // 考勤类型（签到、签退）
    private \DateTimeInterface $attendanceTime;  // 考勤时间
    private string $attendanceMethod;  // 考勤方式
    private array $attendanceData;  // 考勤数据（照片、指纹等）
    private bool $isValid;  // 是否有效
    private string $verificationResult;  // 验证结果
    private \DateTimeInterface $createTime;
}
```

#### ClassroomSchedule（教室排课）

```php
class ClassroomSchedule
{
    private string $id;
    private Classroom $classroom;
    private string $teacherId;  // 教师ID
    private \DateTimeInterface $scheduleDate;  // 排课日期
    private \DateTimeInterface $startTime;  // 开始时间
    private \DateTimeInterface $endTime;  // 结束时间
    private string $scheduleType;  // 排课类型
    private string $scheduleStatus;  // 排课状态
    private array $scheduleConfig;  // 排课配置
    private string $remark;  // 备注
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
}
```

## 4. 服务设计

### 4.1 现有服务增强

#### ClassroomService

```php
class ClassroomService
{
    // 现有方法保持不变
    
    // 新增方法
    public function getClassroomCapacity(string $classroomId): array;
    public function getClassroomUtilization(string $classroomId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;
    public function validateClassroomRequirements(string $classroomId, array $requirements): bool;
}
```

### 4.2 新增服务

#### AttendanceService

```php
class AttendanceService
{
    public function recordAttendance(string $registrationId, array $attendanceData): AttendanceRecord;
    public function validateAttendance(string $recordId): bool;
    public function getAttendanceStatistics(string $classroomId, \DateTimeInterface $date): array;
    public function generateAttendanceReport(string $classroomId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array;
    public function detectAttendanceAnomalies(string $classroomId): array;
}
```

#### ScheduleService

```php
class ScheduleService
{
    public function createSchedule(string $classroomId, array $scheduleData): ClassroomSchedule;
    public function updateSchedule(string $scheduleId, array $scheduleData): ClassroomSchedule;
    public function checkScheduleConflict(string $classroomId, \DateTimeInterface $startTime, \DateTimeInterface $endTime): array;
    public function getClassroomSchedule(string $classroomId, \DateTimeInterface $date): array;
    public function generateScheduleReport(string $classroomId): array;
}
```

## 5. Command设计

### 5.1 教室管理命令

#### ClassroomCapacityOptimizeCommand

```php
class ClassroomCapacityOptimizeCommand extends Command
{
    protected static $defaultName = 'classroom:capacity:optimize';
    
    // 优化教室容量配置
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.2 考勤管理命令

#### AttendanceReportCommand

```php
class AttendanceReportCommand extends Command
{
    protected static $defaultName = 'classroom:attendance:report';
    
    // 生成考勤报告（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

### 5.3 统计分析命令

#### ClassroomStatisticsCommand

```php
class ClassroomStatisticsCommand extends Command
{
    protected static $defaultName = 'classroom:statistics:generate';
    
    // 生成教室使用统计（每日执行）
    public function execute(InputInterface $input, OutputInterface $output): int;
}
```

## 6. 依赖包

- `train-course-bundle` - 课程管理
- `exam-bundle` - 题库和分类
- `train-record-bundle` - 学习记录
- `face-detect-bundle` - 人脸识别
- `doctrine-entity-checker-bundle` - 实体检查
- `doctrine-timestamp-bundle` - 时间戳管理

## 7. 测试计划

### 7.1 单元测试

- [ ] Classroom实体测试
- [ ] Registration实体测试
- [ ] ClassroomService测试
- [ ] AttendanceService测试

### 7.2 集成测试

- [ ] 完整报班流程测试
- [ ] 教室排课管理测试

---

**文档版本**: v1.0
**创建日期**: 2024年12月
**负责人**: 开发团队
