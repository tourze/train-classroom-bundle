# TrainClassroomBundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/train-classroom-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-classroom-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/train-classroom-bundle/ci.yml?branch=master&style=flat-square)](https://github.com/tourze/train-classroom-bundle/actions)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tourze/train-classroom-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/train-classroom-bundle/?branch=master)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/train-classroom-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/train-classroom-bundle)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/symfony-%3E%3D6.4-000000.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)

[English](README.md) | [中文](README.zh-CN.md)

A comprehensive training classroom management bundle for safety production training systems, providing complete classroom management, attendance management, and course scheduling functionalities.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Commands](#commands)
- [API Reference](#api-reference)
- [Data Models](#data-models)
- [Advanced Usage](#advanced-usage)
- [Development](#development)
- [Testing](#testing)
- [License](#license)

## Features

### 🎯 Core Features

- **Attendance Management** - Multiple attendance methods support (face recognition, card reader, fingerprint, QR code, etc.)
- **Schedule Management** - Smart scheduling, conflict detection, resource allocation
- **Classroom Management** - Physical/virtual classrooms, facility configuration, environment monitoring
- **Data Analytics** - Attendance rate statistics, utilization analysis, anomaly detection
- **Archive Management** - "One Session One File", training records, video archiving

### 🏗️ Technical Features

- **DDD Architecture** - Domain-Driven Design with clear responsibilities
- **RESTful API** - Complete REST API interfaces
- **Multi-tenancy Support** - Multi-tenant support via supplierId
- **Audit Trail** - Complete operation audit records
- **Flexible Configuration** - Rich configuration options
- **CLI Tools** - Data synchronization, cleanup, and maintenance tools
- **Event-Driven** - Event-based architecture for extensibility
- **PHP 8.1+** - Modern PHP features and strict typing
- **Symfony 6.4+** - Built on stable Symfony components

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Doctrine ORM 3.0 or higher
- MySQL 5.7+ or PostgreSQL 10+
- Bundle Dependencies:
  - `tourze/doctrine-snowflake-bundle` - For ID generation
  - `tourze/doctrine-timestamp-bundle` - For timestamp management
  - `tourze/doctrine-indexed-bundle` - For index management
  - `tourze/idcard-manage-bundle` - For ID card validation
  - `tourze/train-course-bundle` - For course management integration
  - `tourze/train-category-bundle` - For category management

## Installation

### 1. Install via Composer

```bash
composer require tourze/train-classroom-bundle
```

### 2. Register the Bundle

Add to your `config/bundles.php`:

```php
return [
    // ...
    Tourze\TrainClassroomBundle\TrainClassroomBundle::class => ['all' => true],
];
```

### 3. Configure the Bundle

This bundle uses environment variables and service configuration instead of YAML configuration files. All configurations are handled through service parameters and environment variables.

Configure your services in `config/services.yaml`:

```yaml
services:
  # Import bundle services
  _instanceof:
    Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface:
      tags: ['train_classroom.attendance_service']
    Tourze\TrainClassroomBundle\Service\ScheduleServiceInterface:
      tags: ['train_classroom.schedule_service']
```

Set environment variables in your `.env` file:

```bash
# Attendance settings
ATTENDANCE_FACE_RECOGNITION_ENABLED=true
ATTENDANCE_CARD_READER_ENABLED=true
ATTENDANCE_QR_CODE_ENABLED=true
ATTENDANCE_SIGN_IN_TOLERANCE_MINUTES=15
ATTENDANCE_SIGN_OUT_TOLERANCE_MINUTES=15

# Schedule settings
SCHEDULE_DEFAULT_DURATION_MINUTES=120
SCHEDULE_MIN_BREAK_MINUTES=15
SCHEDULE_MAX_ADVANCE_BOOKING_DAYS=90

# Archive settings
ARCHIVE_ATTENDANCE_RETENTION_DAYS=1095
ARCHIVE_VIDEO_RETENTION_DAYS=365
```

### 4. Create Database Tables

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Configuration

This bundle uses environment variables for configuration. Set these in your `.env` file:

### Attendance Configuration

```bash
# Enable/disable attendance methods
ATTENDANCE_FACE_RECOGNITION_ENABLED=true
ATTENDANCE_FINGERPRINT_ENABLED=false
ATTENDANCE_CARD_READER_ENABLED=true
ATTENDANCE_QR_CODE_ENABLED=true

# Tolerance settings (in minutes)
ATTENDANCE_SIGN_IN_TOLERANCE_MINUTES=15
ATTENDANCE_SIGN_OUT_TOLERANCE_MINUTES=15

# Allow makeup attendance
ATTENDANCE_ALLOW_MAKEUP=true
```

### Schedule Configuration

```bash
# Schedule defaults
SCHEDULE_DEFAULT_DURATION_MINUTES=120
SCHEDULE_MIN_BREAK_MINUTES=15
SCHEDULE_ALLOW_OVERLAPPING=false
SCHEDULE_MAX_ADVANCE_BOOKING_DAYS=90
```

### Classroom Configuration

```bash
# Monitoring settings
CLASSROOM_ENABLE_MONITORING=true
CLASSROOM_ENABLE_ENVIRONMENT_MONITORING=false
```

### Archive Configuration

```bash
# Data retention settings (in days)
ARCHIVE_ATTENDANCE_RETENTION_DAYS=1095  # 3 years
ARCHIVE_VIDEO_RETENTION_DAYS=365        # 1 year
ARCHIVE_ENABLE_AUTO_CLEANUP=true
```

## Usage

### Attendance Management

#### Record Attendance

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

#### Get Attendance Statistics

```bash
curl /api/attendance/statistics/123
```

#### Detect Attendance Anomalies

```bash
curl /api/attendance/anomalies/123?date=2025-05-27
```

#### Makeup Attendance

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

### Schedule Management

#### Create Schedule

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

#### Detect Schedule Conflicts

```bash
curl -X POST /api/schedule/conflicts \
  -H "Content-Type: application/json" \
  -d '{
    "classroom_id": 1,
    "start_time": "2025-05-28 09:00:00",
    "end_time": "2025-05-28 11:00:00"
  }'
```

#### Find Available Classrooms

```bash
curl "/api/schedule/available-classrooms?start_time=2025-05-28 09:00:00&end_time=2025-05-28 11:00:00&min_capacity=20"
```

#### Get Schedule Calendar

```bash
curl "/api/schedule/calendar?start_date=2025-05-01&end_date=2025-05-31&classroom_ids=1,2,3"
```

## Commands

### Sync Attendance Data

Import from CSV file:
```bash
php bin/console train-classroom:sync-attendance file --file=/path/to/attendance.csv
```

Sync from API endpoint:
```bash
php bin/console train-classroom:sync-attendance api --api-url=https://device.example.com/api/attendance
```

Dry run mode:
```bash
php bin/console train-classroom:sync-attendance file --file=/path/to/data.csv --dry-run
```

### Cleanup Expired Data

Using default configuration:
```bash
php bin/console train-classroom:cleanup-data
```

Custom retention days:
```bash
php bin/console train-classroom:cleanup-data --attendance-days=365 --video-days=180
```

Dry run mode:
```bash
php bin/console train-classroom:cleanup-data --dry-run
```

### Update Schedule Status

Automatically update schedule status (recommended for cron jobs):
```bash
php bin/console train-classroom:update-schedule-status
```

Dry run mode:
```bash
php bin/console train-classroom:update-schedule-status --dry-run
```

Set batch size:
```bash
php bin/console train-classroom:update-schedule-status --batch-size=50
```

### Expire Registration Records

Automatically mark expired registration records (cron job):
```bash
php bin/console job-training:expire-registration
```

> Note: This command runs automatically every minute, no manual execution needed.

## Data Models

### Core Entities

#### AttendanceRecord
- `id` - Primary key (Snowflake ID)
- `registration` - Related registration record
- `type` - Attendance type (sign_in, sign_out, break_out, break_return)
- `method` - Attendance method (face, card, fingerprint, qr_code, manual, mobile)
- `recordTime` - Record timestamp
- `verificationResult` - Verification result
- `deviceData` - Device data (JSON)
- `remark` - Remarks

#### ClassroomSchedule
- `id` - Primary key (Snowflake ID)
- `classroom` - Related classroom
- `courseId` - Course ID
- `type` - Schedule type (regular, makeup, exam, meeting, training, lecture)
- `status` - Schedule status (scheduled, in_progress, completed, cancelled, paused, postponed)
- `startTime` - Start time
- `endTime` - End time
- `title` - Title
- `instructorId` - Instructor ID
- `maxParticipants` - Maximum participants

#### Classroom

- `id` - Primary key (Snowflake ID)
- `name` - Classroom name
- `type` - Classroom type (physical, virtual, hybrid)
- `status` - Classroom status (active, inactive, maintenance, archived)
- `capacity` - Maximum capacity
- `location` - Physical location
- `features` - Available features (JSON)
- `supplierId` - Tenant ID

#### Registration

- `id` - Primary key (Snowflake ID)
- `userId` - User ID
- `courseId` - Course ID
- `classroomId` - Classroom ID
- `status` - Registration status
- `learnStatus` - Learning status
- `registrationTime` - Registration timestamp

### Enumerations

- `AttendanceType` - Attendance types (SIGN_IN, SIGN_OUT, BREAK_OUT, BREAK_RETURN)
- `AttendanceMethod` - Attendance methods (FACE, CARD, FINGERPRINT, QR_CODE, MANUAL, MOBILE)
- `VerificationResult` - Verification results (SUCCESS, FAILURE, PENDING, ERROR)
- `ClassroomType` - Classroom types (PHYSICAL, VIRTUAL, HYBRID)
- `ClassroomStatus` - Classroom status (ACTIVE, INACTIVE, MAINTENANCE, ARCHIVED)
- `ScheduleType` - Schedule types (REGULAR, MAKEUP, EXAM, MEETING, TRAINING, LECTURE)
- `ScheduleStatus` - Schedule status (SCHEDULED, IN_PROGRESS, COMPLETED, CANCELLED, PAUSED, POSTPONED)
- `RegistrationLearnStatus` - Learning status (NOT_STARTED, IN_PROGRESS, COMPLETED, FAILED)

## API Reference

### Service Interfaces

### AttendanceServiceInterface

- `recordAttendance()` - Record attendance
- `batchImportAttendance()` - Batch import attendance records
- `getAttendanceStatistics()` - Get attendance statistics
- `getCourseAttendanceSummary()` - Get course attendance summary
- `detectAttendanceAnomalies()` - Detect attendance anomalies
- `makeUpAttendance()` - Makeup attendance
- `validateAttendance()` - Validate attendance
- `getAttendanceRateStatistics()` - Get attendance rate statistics

### ScheduleServiceInterface

- `createSchedule()` - Create schedule
- `detectScheduleConflicts()` - Detect schedule conflicts
- `updateScheduleStatus()` - Update schedule status
- `getClassroomUtilizationRate()` - Get classroom utilization rate
- `findAvailableClassrooms()` - Find available classrooms
- `batchCreateSchedules()` - Batch create schedules
- `cancelSchedule()` - Cancel schedule
- `postponeSchedule()` - Postpone schedule
- `getScheduleCalendar()` - Get schedule calendar
- `getScheduleStatisticsReport()` - Get schedule statistics report

### ClassroomServiceInterface

- `createClassroom()` - Create classroom
- `updateClassroom()` - Update classroom details
- `getClassroomById()` - Get classroom by ID
- `findClassrooms()` - Find classrooms by criteria
- `setClassroomStatus()` - Set classroom status
- `getClassroomFeatures()` - Get classroom features

## Advanced Usage

### Custom Attendance Methods

You can implement custom attendance methods by extending the base attendance service:

```php
<?php

namespace App\Service;

use Tourze\TrainClassroomBundle\Service\AttendanceService;

class CustomAttendanceService extends AttendanceService
{
    public function recordCustomAttendance(Registration $registration, array $customData): bool
    {
        // Custom attendance logic
        return parent::recordAttendance($registration, $type, $method, $customData);
    }
}
```

### Advanced Scheduling

For complex scheduling scenarios, use the schedule service with custom filters:

```php
$criteria = [
    'start_time' => new \DateTime('2024-01-01 09:00:00'),
    'end_time' => new \DateTime('2024-01-01 17:00:00'),
    'classroom_type' => 'PHYSICAL',
    'min_capacity' => 20,
];

$availableClassrooms = $scheduleService->findAvailableClassrooms($criteria);
```

## Development

### Extending Services

To extend the attendance service, create a custom service class:

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

Then replace the default service in your service configuration:

```yaml
services:
  Tourze\TrainClassroomBundle\Service\AttendanceServiceInterface:
    alias: App\Service\CustomAttendanceService
```

### Custom Attendance Device Integration

Implement the device interface to integrate custom attendance devices:

```php
<?php

namespace App\Device;

interface AttendanceDeviceInterface
{
    public function getAttendanceRecords(\DateTimeInterface $since): array;
    public function syncAttendanceRecord(AttendanceRecord $record): bool;
}
```

### Event System

The bundle dispatches several events that you can listen to:

- `AttendanceRecordedEvent` - Dispatched when attendance is recorded
- `ScheduleCreatedEvent` - Dispatched when a schedule is created
- `ScheduleUpdatedEvent` - Dispatched when a schedule is updated
- `ClassroomStatusChangedEvent` - Dispatched when classroom status changes

Example event subscriber:

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
        // Custom logic here
    }
}
```

## Testing

Run the test suite:

```bash
# Run all tests
./vendor/bin/phpunit packages/train-classroom-bundle/tests

# Run specific test class
./vendor/bin/phpunit packages/train-classroom-bundle/tests/Service/AttendanceServiceTest.php

# Run with coverage
./vendor/bin/phpunit packages/train-classroom-bundle/tests --coverage-html=coverage
```

Run static analysis:

```bash
# PHPStan analysis
php -d memory_limit=2G ./vendor/bin/phpstan analyse packages/train-classroom-bundle
```

## License

MIT License

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

If you have any questions or issues, please contact:

- Submit an Issue: [GitHub Issues](https://github.com/tourze/train-classroom-bundle/issues)
- Email: support@tourze.com
