# train-classroom-bundle 实体设计文档

## 实体关系图（ER图）

```
现有实体:
┌─────────────┐    ┌─────────────────┐    ┌─────────────┐
│  Classroom  │────│  Registration   │────│   Student   │
│             │    │                 │    │             │
│ - id        │    │ - id            │    │ - id        │
│ - title     │    │ - classroom     │    │ - realName  │
│ - startTime │    │ - student       │    │ - idCard    │
│ - endTime   │    │ - course        │    │             │
│ - course    │    │ - bank          │    └─────────────┘
│ - bank      │    │ - status        │
│ - category  │    │ - beginTime     │
│             │    │ - endTime       │
└─────────────┘    │ - payTime       │
                   │ - finished      │
                   └─────────────────┘

新增实体关系:
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│ AttendanceRecord│────│   Registration  │────│ClassroomSchedule│
│                 │    │                 │    │                 │
│ - id            │    │ (现有实体)       │    │ - id            │
│ - registration  │    │                 │    │ - classroom     │
│ - attendanceType│    │                 │    │ - teacherId     │
│ - attendanceTime│    │                 │    │ - scheduleDate  │
│ - method        │    │                 │    │ - startTime     │
│ - data          │    │                 │    │ - endTime       │
│ - isValid       │    │                 │    │ - scheduleType  │
│ - verification  │    │                 │    │ - status        │
└─────────────────┘    └─────────────────┘    │ - config        │
                                              └─────────────────┘

增强的Classroom实体:
┌─────────────────────────────────────────────────────────────┐
│                        Classroom                            │
│                                                             │
│ 现有字段:                                                    │
│ - id, title, startTime, endTime, course, bank, category     │
│                                                             │
│ 新增字段:                                                    │
│ - capacity (容量)                                           │
│ - area (面积)                                               │
│ - location (位置)                                           │
│ - facilities (设施配置JSON)                                  │
│ - monitoringDevices (监控设备JSON)                          │
│ - attendanceDevices (考勤设备JSON)                          │
│ - classroomType (教室类型: PHYSICAL/VIRTUAL)                │
│ - status (教室状态: ACTIVE/INACTIVE/MAINTENANCE)            │
│ - safetyEquipment (安全设备JSON)                            │
│ - networkConfig (网络配置JSON)                              │
└─────────────────────────────────────────────────────────────┘
```

## 新增实体详细设计

### 1. AttendanceRecord（考勤记录）

```php
/**
 * 考勤记录实体
 * 记录学员的签到签退信息，支持多种考勤方式
 */
class AttendanceRecord
{
    // 基础字段
    private string $id;                    // 雪花ID
    private Registration $registration;    // 关联报班记录
    private string $attendanceType;        // 考勤类型：SIGN_IN/SIGN_OUT
    private \DateTimeInterface $attendanceTime;  // 考勤时间
    private string $attendanceMethod;      // 考勤方式：FACE/CARD/FINGERPRINT/QR_CODE
    private array $attendanceData;         // 考勤数据（照片、指纹等）
    private bool $isValid;                 // 是否有效
    private string $verificationResult;    // 验证结果：SUCCESS/FAILED/PENDING
    private ?string $deviceId;             // 设备ID
    private ?string $deviceLocation;       // 设备位置
    private ?float $latitude;              // 纬度（移动考勤）
    private ?float $longitude;             // 经度（移动考勤）
    private ?string $remark;               // 备注
    
    // 审计字段
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
    private ?string $createdBy;
    private ?string $updatedBy;
    private ?string $createdFromIp;
    private ?string $updatedFromIp;
    private ?Supplier $supplier;
}
```

### 2. ClassroomSchedule（教室排课）

```php
/**
 * 教室排课实体
 * 管理教室的课程安排和教师分配
 */
class ClassroomSchedule
{
    // 基础字段
    private string $id;                    // 雪花ID
    private Classroom $classroom;          // 关联教室
    private string $teacherId;             // 教师ID
    private \DateTimeInterface $scheduleDate;  // 排课日期
    private \DateTimeInterface $startTime;     // 开始时间
    private \DateTimeInterface $endTime;       // 结束时间
    private string $scheduleType;          // 排课类型：REGULAR/MAKEUP/EXAM/MEETING
    private string $scheduleStatus;        // 排课状态：SCHEDULED/ONGOING/COMPLETED/CANCELLED
    private array $scheduleConfig;         // 排课配置（重复规则等）
    private ?string $courseContent;        // 课程内容
    private ?int $expectedStudents;        // 预期学员数
    private ?int $actualStudents;          // 实际学员数
    private ?string $remark;               // 备注
    
    // 审计字段
    private \DateTimeInterface $createTime;
    private \DateTimeInterface $updateTime;
    private ?string $createdBy;
    private ?string $updatedBy;
    private ?Supplier $supplier;
}
```

### 3. 增强的Classroom实体

```php
/**
 * 增强的教室实体
 * 添加物理环境和设备管理功能
 */
class Classroom // 现有实体增强
{
    // 现有字段保持不变...
    
    // 新增物理环境字段
    private ?int $capacity;                // 容量（人数）
    private ?float $area;                  // 面积（平方米）
    private ?string $location;             // 位置描述
    private array $facilities;             // 设施配置JSON
    private array $monitoringDevices;      // 监控设备JSON
    private array $attendanceDevices;      // 考勤设备JSON
    private string $classroomType;         // 教室类型：PHYSICAL/VIRTUAL
    private string $status;                // 教室状态：ACTIVE/INACTIVE/MAINTENANCE
    private array $safetyEquipment;        // 安全设备JSON
    private array $networkConfig;          // 网络配置JSON
    private ?string $emergencyPlan;        // 应急预案
    private ?float $temperature;           // 温度
    private ?float $humidity;              // 湿度
    private ?int $lightLevel;              // 光照强度
    private ?int $noiseLevel;              // 噪音等级
    
    // 新增关联
    private Collection $schedules;         // 排课记录
    private Collection $attendanceRecords; // 考勤记录（通过Registration关联）
}
```

## 实体关系说明

### 1. 核心关系

- **Classroom ↔ Registration**: 一对多关系（现有）
- **Registration ↔ AttendanceRecord**: 一对多关系（新增）
- **Classroom ↔ ClassroomSchedule**: 一对多关系（新增）

### 2. 扩展关系

- **AttendanceRecord**: 通过Registration间接关联到Classroom、Student、Course
- **ClassroomSchedule**: 直接关联到Classroom，通过teacherId关联到教师系统
- **增强的Classroom**: 包含设备配置、环境监控等信息

### 3. 数据流向

```
学员报班 → Registration → 生成考勤计划 → AttendanceRecord
教室管理 → Classroom → 排课管理 → ClassroomSchedule
考勤数据 → AttendanceRecord → 统计分析 → 报表生成
```

## 数据库表设计

### 1. job_training_attendance_record

```sql
CREATE TABLE job_training_attendance_record (
    id BIGINT PRIMARY KEY COMMENT 'ID',
    registration_id BIGINT NOT NULL COMMENT '报班记录ID',
    attendance_type VARCHAR(20) NOT NULL COMMENT '考勤类型',
    attendance_time DATETIME NOT NULL COMMENT '考勤时间',
    attendance_method VARCHAR(20) NOT NULL COMMENT '考勤方式',
    attendance_data JSON COMMENT '考勤数据',
    is_valid BOOLEAN DEFAULT TRUE COMMENT '是否有效',
    verification_result VARCHAR(20) NOT NULL COMMENT '验证结果',
    device_id VARCHAR(100) COMMENT '设备ID',
    device_location VARCHAR(200) COMMENT '设备位置',
    latitude DECIMAL(10,8) COMMENT '纬度',
    longitude DECIMAL(11,8) COMMENT '经度',
    remark TEXT COMMENT '备注',
    create_time DATETIME COMMENT '创建时间',
    update_time DATETIME COMMENT '更新时间',
    created_by VARCHAR(100) COMMENT '创建人',
    updated_by VARCHAR(100) COMMENT '更新人',
    created_from_ip VARCHAR(128) COMMENT '创建时IP',
    updated_from_ip VARCHAR(128) COMMENT '更新时IP',
    supplier_id BIGINT COMMENT '供应商ID',
    INDEX idx_registration_id (registration_id),
    INDEX idx_attendance_time (attendance_time),
    INDEX idx_attendance_type (attendance_type),
    INDEX idx_supplier_id (supplier_id)
) COMMENT='考勤记录表';
```

### 2. job_training_classroom_schedule

```sql
CREATE TABLE job_training_classroom_schedule (
    id BIGINT PRIMARY KEY COMMENT 'ID',
    classroom_id BIGINT NOT NULL COMMENT '教室ID',
    teacher_id VARCHAR(100) NOT NULL COMMENT '教师ID',
    schedule_date DATE NOT NULL COMMENT '排课日期',
    start_time DATETIME NOT NULL COMMENT '开始时间',
    end_time DATETIME NOT NULL COMMENT '结束时间',
    schedule_type VARCHAR(20) NOT NULL COMMENT '排课类型',
    schedule_status VARCHAR(20) NOT NULL COMMENT '排课状态',
    schedule_config JSON COMMENT '排课配置',
    course_content TEXT COMMENT '课程内容',
    expected_students INT COMMENT '预期学员数',
    actual_students INT COMMENT '实际学员数',
    remark TEXT COMMENT '备注',
    create_time DATETIME COMMENT '创建时间',
    update_time DATETIME COMMENT '更新时间',
    created_by VARCHAR(100) COMMENT '创建人',
    updated_by VARCHAR(100) COMMENT '更新人',
    supplier_id BIGINT COMMENT '供应商ID',
    INDEX idx_classroom_id (classroom_id),
    INDEX idx_schedule_date (schedule_date),
    INDEX idx_teacher_id (teacher_id),
    INDEX idx_supplier_id (supplier_id),
    UNIQUE KEY uk_classroom_time (classroom_id, start_time, end_time)
) COMMENT='教室排课表';
```

### 3. job_training_classroom表增强

```sql
-- 为现有表添加新字段
ALTER TABLE job_training_classroom 
ADD COLUMN capacity INT COMMENT '容量',
ADD COLUMN area DECIMAL(8,2) COMMENT '面积',
ADD COLUMN location VARCHAR(200) COMMENT '位置',
ADD COLUMN facilities JSON COMMENT '设施配置',
ADD COLUMN monitoring_devices JSON COMMENT '监控设备',
ADD COLUMN attendance_devices JSON COMMENT '考勤设备',
ADD COLUMN classroom_type VARCHAR(20) DEFAULT 'PHYSICAL' COMMENT '教室类型',
ADD COLUMN status VARCHAR(20) DEFAULT 'ACTIVE' COMMENT '教室状态',
ADD COLUMN safety_equipment JSON COMMENT '安全设备',
ADD COLUMN network_config JSON COMMENT '网络配置',
ADD COLUMN emergency_plan TEXT COMMENT '应急预案',
ADD COLUMN temperature DECIMAL(4,1) COMMENT '温度',
ADD COLUMN humidity DECIMAL(4,1) COMMENT '湿度',
ADD COLUMN light_level INT COMMENT '光照强度',
ADD COLUMN noise_level INT COMMENT '噪音等级';
```

## 枚举类型设计

### 1. AttendanceType（考勤类型）

```php
enum AttendanceType: string
{
    case SIGN_IN = 'SIGN_IN';      // 签到
    case SIGN_OUT = 'SIGN_OUT';    // 签退
    case BREAK_OUT = 'BREAK_OUT';  // 休息外出
    case BREAK_IN = 'BREAK_IN';    // 休息返回
}
```

### 2. AttendanceMethod（考勤方式）

```php
enum AttendanceMethod: string
{
    case FACE = 'FACE';                    // 人脸识别
    case CARD = 'CARD';                    // 刷卡
    case FINGERPRINT = 'FINGERPRINT';      // 指纹
    case QR_CODE = 'QR_CODE';             // 二维码
    case MANUAL = 'MANUAL';               // 手动
    case MOBILE = 'MOBILE';               // 移动端
}
```

### 3. ClassroomType（教室类型）

```php
enum ClassroomType: string
{
    case PHYSICAL = 'PHYSICAL';   // 物理教室
    case VIRTUAL = 'VIRTUAL';     // 虚拟教室
    case HYBRID = 'HYBRID';       // 混合教室
}
```

### 4. ClassroomStatus（教室状态）

```php
enum ClassroomStatus: string
{
    case ACTIVE = 'ACTIVE';           // 活跃
    case INACTIVE = 'INACTIVE';       // 非活跃
    case MAINTENANCE = 'MAINTENANCE'; // 维护中
    case RESERVED = 'RESERVED';       // 预留
}
```

### 5. ScheduleType（排课类型）

```php
enum ScheduleType: string
{
    case REGULAR = 'REGULAR';     // 常规课程
    case MAKEUP = 'MAKEUP';       // 补课
    case EXAM = 'EXAM';          // 考试
    case MEETING = 'MEETING';     // 会议
    case TRAINING = 'TRAINING';   // 培训
}
```

### 6. ScheduleStatus（排课状态）

```php
enum ScheduleStatus: string
{
    case SCHEDULED = 'SCHEDULED';   // 已排课
    case ONGOING = 'ONGOING';       // 进行中
    case COMPLETED = 'COMPLETED';   // 已完成
    case CANCELLED = 'CANCELLED';   // 已取消
    case POSTPONED = 'POSTPONED';   // 已延期
}
```

---

**文档版本**: v1.0  
**创建日期**: 2025年05月27日  
**更新日期**: 2025年05月27日 