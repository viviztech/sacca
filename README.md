# Sacca — Aviation Training Institute Management System

A full-featured institute management platform built for aviation training institutes with multi-branch support. Covers HR, academics, placement, payroll, compliance, and student lifecycle management.

**Built with:** Laravel 13.8 · PHP 8.4 · Livewire 3 · TailwindCSS 4 · MySQL · Laravel Sanctum · Redis

---

## Table of Contents

1. [Features](#features)
2. [Tech Stack](#tech-stack)
3. [Requirements](#requirements)
4. [Local Setup (Step by Step)](#local-setup)
5. [Environment Configuration](#environment-configuration)
6. [Database Setup](#database-setup)
7. [Running the Application](#running-the-application)
8. [Demo Login Credentials](#demo-login-credentials)
9. [Flutter Mobile API](#flutter-mobile-api)
10. [Scheduled Tasks](#scheduled-tasks)
11. [Queue Workers](#queue-workers)
12. [Laravel Cloud Deployment](#laravel-cloud-deployment)
13. [Project Structure](#project-structure)
14. [Testing](#testing)
15. [WhatsApp Templates](#whatsapp-templates)

---

## Features

| Module | Description |
|---|---|
| **Attendance** | GPS-based mobile check-in/out, geo-fence per branch, live dashboard (auto-refresh 30s) |
| **In/Out Monitoring** | Break tracking, overtime calculation, inout_logs per event |
| **Leave Management** | Request/approval workflow, 5 leave types, balance tracking |
| **Daily Work Reports** | EOD submission, 4:30 PM reminder, 7 PM escalation to manager |
| **Task Board** | Kanban (Pending → In Progress → Completed), priority, deadline alerts |
| **Faculty Timetable** | Builder with conflict detection (faculty + batch overlap), week view |
| **Student Attendance** | Faculty bulk-mark by class, locks on submit, WhatsApp absentee alerts |
| **LMS** | Notes, videos, links, assignments, timed quizzes (MCQ / T-F / Short Answer) |
| **Training Hours** | Theory / Practical / Simulation logs per batch |
| **Placement CRM** | Company database, drive scheduler, auto-shortlisting, interview invites |
| **Grooming Inspection** | Daily 5-point checklist (Uniform, Hair, Nails, Shoes, ID Card) |
| **Airport/Industrial Visits** | Plan visits, upload permission forms, track participant attendance |
| **Payroll** | Attendance-based salary, PDF payslips via DomPDF |
| **Document Vault** | Upload, categorise (certificate/passport/visa etc.), expiry alerts |
| **Compliance Reports** | TAHDCO / SCDD / Monthly / Annual PDF generator |
| **Announcements** | Publish to all or targeted roles/branches, FCM push on publish |
| **Complaints Portal** | Anonymous submissions, category filter, resolver workflow |
| **Visitor Management** | Check-in/out, badge, host tracking |
| **AI Smart Alerts** | Faculty absent streak, missing reports, low student attendance, placement nudge |
| **Multi-Branch** | Coimbatore + Kerala, Super Admin cross-branch KPI dashboard |
| **Role-Aware Dashboard** | Unique dashboard per role (Super Admin, Branch Admin, Faculty, Student, Placement Officer) |

---

## Tech Stack

| Layer | Technology | Version |
|---|---|---|
| Backend | Laravel | 13.8 |
| Language | PHP | 8.4 |
| Frontend | Livewire + TailwindCSS | 3.x / 4.x |
| Database | MySQL | 8.x |
| Queue / Cache / Session | Redis | 7.x |
| Mobile API Auth | Laravel Sanctum | 4.x |
| File Storage | S3 / Cloudflare R2 (local disk in dev) | — |
| PDF Generation | barryvdh/laravel-dompdf | 3.x |
| Push Notifications | kreait/firebase-php (FCM) | 8.x |
| WhatsApp | Meta Cloud API (via port 8003) | — |
| AI Assistance | Laravel Boost (MCP) | 2.x |

---

## Requirements

- PHP >= 8.4 with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `intl`
- Composer >= 2.8
- Node.js >= 22.x and npm >= 10.x
- MySQL >= 8.0
- Redis >= 7.x (optional for dev — falls back to database driver)

---

## Local Setup

Follow these steps **in order**:

### Step 1 — Clone the Repository

```bash
git clone https://github.com/viviztech/sacca.git
cd sacca
```

### Step 2 — Install PHP Dependencies

```bash
composer install
```

### Step 3 — Install Node Dependencies

```bash
npm install
```

### Step 4 — Create Environment File

```bash
cp .env.example .env
```

### Step 5 — Generate Application Key

```bash
php artisan key:generate
```

### Step 6 — Configure Environment

Edit `.env` with your local settings (see [Environment Configuration](#environment-configuration) below).

**Minimum required changes:**
```env
APP_URL=http://localhost:8012

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sacca
DB_USERNAME=root
DB_PASSWORD=          # your MySQL password

MAIL_MAILER=log       # keeps emails in storage/logs/laravel.log for local dev
```

### Step 7 — Run Migrations

```bash
php artisan migrate
```

### Step 8 — Seed Test Data

```bash
php artisan db:seed
```

This seeds:
- 2 branches (Coimbatore + Kerala)
- 13 users covering all 8 roles
- 5 aviation courses + 5 batches + 7 enrollments
- 45 timetable slots
- 240 attendance records (30 days)
- 5 LMS materials + quiz questions
- 5 companies + 4 placement drives + 9 applications
- 7 tasks, 6 announcements, 4 complaints, 4 visitor logs
- 18 grooming inspections, 30 work reports, 13 leave requests

### Step 9 — Build Frontend Assets

```bash
npm run build
```

For development with hot-reload:
```bash
npm run dev
```

### Step 10 — Link Storage

```bash
php artisan storage:link
```

### Step 11 — Start the Server

```bash
php artisan serve --port=8012
```

Open **http://localhost:8012** in your browser.

---

## Environment Configuration

### Database (MySQL)
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sacca
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Queue Driver
```env
# Local development (no Redis required):
QUEUE_CONNECTION=database

# Production (recommended):
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Session & Cache
```env
# Local:
SESSION_DRIVER=database
CACHE_STORE=database

# Production:
SESSION_DRIVER=redis
CACHE_STORE=redis
```

### File Storage
```env
# Local:
FILESYSTEM_DISK=local

# Production (Laravel Cloud / Cloudflare R2):
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your_bucket
AWS_ENDPOINT=https://your-account.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### WhatsApp (Meta Cloud API)
```env
WHATSAPP_API_URL=http://localhost:8003/api
```
> Sacca calls the existing WhatsApp project running on port 8003 internally.

### Firebase FCM (Push Notifications)
```env
FIREBASE_CREDENTIALS=/absolute/path/to/firebase-credentials.json
```

### Mail
```env
# Local (logs to storage/logs/laravel.log):
MAIL_MAILER=log

# Production:
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sacca.in
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@sacca.in
MAIL_FROM_NAME="Sacca Institute"
```

---

## Database Setup

### Fresh Setup
```bash
php artisan migrate:fresh --seed
```

### Run Only Migrations
```bash
php artisan migrate
```

### Run Only Seeders
```bash
php artisan db:seed
```

### Run a Specific Seeder
```bash
php artisan db:seed --class=AttendanceSeeder
```

### Seeder Execution Order
The `DatabaseSeeder` calls them in dependency order:
```
BranchSeeder → UserSeeder → LeaveTypeSeeder → CourseSeeder → BatchSeeder
→ EnrollmentSeeder → TimetableSeeder → AttendanceSeeder → LeaveRequestSeeder
→ WorkReportSeeder → LmsSeeder → PlacementSeeder → TaskSeeder
→ AnnouncementSeeder → ComplaintSeeder → VisitorSeeder → GroomingSeeder
```

---

## Running the Application

### Development (all-in-one)
```bash
# Terminal 1 — PHP server
php artisan serve --port=8012

# Terminal 2 — Vite hot-reload
npm run dev

# Terminal 3 — Queue worker (needed for notifications, PDF generation)
php artisan queue:work --queue=notifications,reports,default
```

### Production Build
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Demo Login Credentials

All accounts use password: **`Admin@1234`**

| Role | Email | Branch | Notes |
|---|---|---|---|
| Super Admin | admin@sacca.in | All | Full access, cross-branch overview |
| Branch Admin (CBE) | cbe.admin@sacca.in | Coimbatore | Manages CBE branch |
| Branch Admin (KER) | ker.admin@sacca.in | Kerala | Manages Kerala branch |
| HR Manager | hr@sacca.in | Coimbatore | Attendance, leave, payroll |
| Academic Coordinator | academic@sacca.in | Coimbatore | Timetable, LMS, student attendance |
| Faculty | faculty@sacca.in | Coimbatore | Class attendance, LMS uploads |
| Student | student@sacca.in | Coimbatore | LMS, placement, leave |
| Placement Officer | placement@sacca.in | Coimbatore | Placement CRM, drives |

**Demo Students** (password: `Student@1234`):
- priya.sharma@student.sacca.in
- arjun.patel@student.sacca.in
- meera.nair@student.sacca.in
- karthik.raj@student.sacca.in
- divya.menon@student.sacca.in

### Password Reset
Forgot password flow available at `/forgot-password`. In local dev, the reset email is written to `storage/logs/laravel.log`.

---

## Flutter Mobile API

**Base URL:** `{APP_URL}/api/v1/`

**Authentication:** Bearer token via Laravel Sanctum

### Authentication
```
POST   /auth/login              # Returns token + user object
POST   /auth/logout
GET    /auth/me
POST   /auth/fcm-token          # Register device FCM token
```

### Profile
```
GET    /profile
PATCH  /profile
PATCH  /profile/password
POST   /profile/avatar
```

### Attendance (GPS)
```
POST   /attendance/check-in     # { lat, lng, accuracy, device_id }
POST   /attendance/check-out    # { lat, lng }
GET    /attendance/today
GET    /attendance/history      # ?month=&year=
GET    /attendance/summary
```

### Leave
```
GET    /leaves
POST   /leaves
GET    /leaves/balances
GET    /leaves/{id}
PATCH  /leaves/{id}/cancel
```

### Timetable
```
GET    /timetable/week          # ?date=YYYY-MM-DD
```

### Student Attendance (Faculty)
```
GET    /student-attendance/class/{timetableId}   # ?date=
POST   /student-attendance/class/{timetableId}   # bulk mark
```

### LMS
```
GET    /lms/materials           # ?batch_id=
GET    /lms/materials/{id}
POST   /lms/assignments/{id}/submit
GET    /lms/quizzes/{id}/start
POST   /lms/quizzes/{id}/submit
```

### Placement
```
GET    /placement/drives
POST   /placement/drives/{id}/apply
GET    /placement/my-applications
```

### Tasks
```
GET    /tasks                   # ?status=
PATCH  /tasks/{id}/status
```

### Announcements
```
GET    /announcements
GET    /announcements/{id}
```

### Work Reports
```
GET    /work-reports            # ?date=
POST   /work-reports
```

### Payslips
```
GET    /payslips
GET    /payslips/{id}/download  # Returns signed storage URL
```

### Notifications
```
GET    /notifications
```

**Rate Limits:**
- Login: 10 requests/minute
- Check-in / Check-out: 5 requests/minute
- All other endpoints: standard Laravel throttle

**Response envelope:**
```json
{
  "success": true,
  "data": {},
  "message": "OK",
  "meta": { "page": 1, "total": 120 }
}
```

---

## Scheduled Tasks

The Laravel scheduler handles all cron jobs. Add this single entry to your server crontab:

```cron
* * * * * cd /var/www/sacca && php artisan schedule:run >> /dev/null 2>&1
```

### Schedule
| Time | Command | Description |
|---|---|---|
| 00:05 daily | `attendance:mark-absent` | Mark all unchecked staff as absent |
| 00:30 daily | `attendance:send-alerts` | Dispatch WhatsApp alerts to absent staff |
| 07:00 daily | `alerts:run-smart-checks` | Run AI alert rules (streak, low attendance, etc.) |
| 16:30 weekdays | `reports:send-work-reminder` | Remind staff to submit EOD report |
| 19:00 weekdays | `reports:escalate-missing` | Escalate missing reports to manager |
| Monday 08:00 | `leaves:send-balance-summary` | Send weekly leave balance to all staff |

View schedule:
```bash
php artisan schedule:list
```

---

## Queue Workers

### Named Queues
| Queue | Workers | Purpose |
|---|---|---|
| `notifications` | 2 | WhatsApp, email, FCM push (high priority) |
| `reports` | 1 | PDF generation (payslips, compliance reports) |
| `default` | 1 | Everything else |

### Run Locally
```bash
php artisan queue:work --queue=notifications,reports,default
```

### Production (Supervisor config in `cloud.yml`)
```ini
[program:sacca-notifications]
command=php artisan queue:work redis --queue=notifications --tries=3 --timeout=30
numprocs=2

[program:sacca-reports]
command=php artisan queue:work redis --queue=reports --tries=2 --timeout=300
numprocs=1

[program:sacca-default]
command=php artisan queue:work redis --queue=default --tries=3 --timeout=60
numprocs=1
```

---

## Laravel Cloud Deployment

The project is pre-configured for [Laravel Cloud](https://cloud.laravel.com) via `cloud.yml`.

### Steps

**1. Create project on Laravel Cloud**
- Go to [cloud.laravel.com](https://cloud.laravel.com)
- Click **New Project** → connect GitHub → select `viviztech/sacca`

**2. Provision resources**
- MySQL database (Laravel Cloud auto-injects credentials)
- Redis (auto-injected)
- Object Storage bucket — Cloudflare R2 (auto-injected)

**3. Set environment variables manually**
```
APP_NAME=Sacca
APP_ENV=production
APP_DEBUG=false
WHATSAPP_API_URL=http://localhost:8003/api
FIREBASE_CREDENTIALS=/path/to/firebase-credentials.json
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_USERNAME=noreply@sacca.in
MAIL_PASSWORD=your_password
```
> Laravel Cloud automatically injects: `DB_*`, `REDIS_*`, `AWS_*` for provisioned resources.

**4. Deploy**
- Click **Deploy** — `cloud.yml` handles the rest:
  - Installs dependencies (no-dev)
  - Builds frontend assets
  - Caches config, routes, views, events
  - Runs `php artisan migrate --force`
  - Starts 4 queue workers (2 notification + 1 report + 1 default)
  - Enables Laravel scheduler

**5. Auto-deploy on push**
Every `git push origin main` triggers a new deployment automatically.

---

## Project Structure

```
app/
├── Console/Commands/       # Artisan commands (attendance, alerts, reports)
├── Enums/                  # UserRole, AttendanceStatus, LeaveStatus, etc.
├── Http/
│   ├── Controllers/Api/V1/ # Flutter REST API controllers
│   └── Middleware/         # RoleMiddleware, BranchScope
├── Jobs/                   # Queue jobs (WhatsApp, FCM, PDF generation)
├── Livewire/               # 31 Livewire page components
│   ├── Academic/           # StudentAttendanceForm, TrainingHourDashboard
│   ├── Admin/              # BranchIndex/Form, UserIndex/Form
│   ├── Announcements/      # AnnouncementManager
│   ├── Attendance/         # AttendanceDashboard, GeoFenceManager
│   ├── Auth/               # Login, ForgotPassword, ResetPassword
│   ├── Complaints/         # ComplaintPortal
│   ├── Compliance/         # ComplianceManager
│   ├── Dashboard/          # SuperAdminDashboard, BranchAdminDashboard
│   ├── Documents/          # DocumentVault
│   ├── Grooming/           # GroomingInspectionForm
│   ├── Leave/              # LeaveIndex
│   ├── Lms/                # MaterialLibrary, MaterialUpload, QuizAttemptPage
│   ├── Payroll/            # PayslipManager
│   ├── Placement/          # PlacementDriveManager, CompanyIndex
│   ├── Tasks/              # TaskBoard
│   ├── Timetable/          # TimetableBuilder
│   ├── Visitors/           # VisitorCheckIn
│   ├── Visits/             # VisitPlanner
│   └── WorkReports/        # DailyWorkReportPage
├── Models/                 # 43 Eloquent models
├── Modules/                # Business logic grouped by domain
│   ├── Academic/Services/  # TimetableConflictService
│   ├── Alerts/Services/    # SmartAlertService
│   ├── Attendance/         # GPSVerificationService, CheckInAction, CheckOutAction
│   ├── Compliance/         # ComplianceReportGenerator
│   ├── Leave/              # ApproveLeaveAction
│   ├── Payroll/            # PayrollGeneratorService, GeneratePayslipPdf
│   └── Placement/          # SendInterviewInviteJob
├── Observers/              # ActivityLogObserver
├── Providers/              # AppServiceProvider (morph map, observers)
└── Support/
    ├── Traits/             # HasRole
    ├── Services/           # FcmService
    └── WhatsAppClient.php

config/
├── sacca_alerts.php        # AI smart alert rules (configurable)
└── ...

database/
├── migrations/             # 47 migrations
└── seeders/                # 17 seeders covering all models

routes/
├── api.php                 # 38 Flutter API endpoints
├── web.php                 # Livewire web routes
└── console.php             # Scheduled commands

cloud.yml                   # Laravel Cloud deployment manifest
```

---

## Testing

```bash
# Run all tests
php artisan test --compact

# Run a specific test file
php artisan test --compact tests/Feature/AttendanceApiTest.php

# Run a specific test
php artisan test --compact --filter=test_user_can_check_in_successfully

# Run unit tests only
php artisan test --compact tests/Unit/
```

**Test coverage:** 94 tests · 182 assertions

| Test File | Tests | What it covers |
|---|---|---|
| `AuthTest` | 6 | Login, logout, redirect, credentials |
| `RoleAccessTest` | 5 | Role middleware, hasRole trait |
| `BranchScopeTest` | 4 | Branch scoping, access control |
| `ApiAuthTest` | 6 | Sanctum token, FCM token, inactive user |
| `AttendanceApiTest` | 9 | GPS check-in/out, geo-fence, accuracy |
| `LeaveApprovalTest` | 5 | Approve/reject, balance updates |
| `GPSVerificationServiceTest` | 4 | Haversine, distance accuracy |
| `TimetableConflictServiceTest` | 5 | Faculty/batch conflict detection |
| `QuizScoringTest` | 6 | MCQ scoring, case-insensitive, pass/fail |
| `TimetableApiTest` | 3 | Week view, bulk student attendance |
| `PayrollGeneratorTest` | 4 | Payslip generation, publish |
| `PlacementApiTest` | 4 | Apply, double-apply guard |
| `GroomingInspectionTest` | 3 | Score calculation |
| `TaskWorkflowTest` | 6 | Status update, overdue detection |
| `AnnouncementApiTest` | 4 | Published/draft/expired filtering |
| `WorkReportApiTest` | 5 | Submit, validate, double-submit |
| `SmartAlertServiceTest` | 4 | Config, rules, thresholds |
| `ExampleTest` | 1 | Root redirect |

---

## WhatsApp Templates

These templates must be pre-approved in Meta Business Manager before going live:

| Template Name | Trigger | Parameters |
|---|---|---|
| `sacca_attendance_absent` | Staff absent — midnight cron | `{name}`, `{date}` |
| `sacca_leave_approved` | Leave approved | `{name}`, `{dates}`, `{leave_type}` |
| `sacca_leave_rejected` | Leave rejected | `{name}`, `{dates}`, `{leave_type}` |
| `sacca_interview_invite` | Student shortlisted | `{name}`, `{company}`, `{date}` |
| `sacca_work_report_reminder` | 4:30 PM weekdays | `{name}`, `{date}` |
| `sacca_work_report_escalation` | 7 PM — report missing | `{staff_name}`, `{date}` |
| `sacca_payslip_ready` | Payslip published | `{name}`, `{month}` |
| `sacca_leave_balance_summary` | Monday 8 AM | `{name}`, `{balance_summary}` |
| `sacca_student_absent_alert` | Student absent streak | `{name}`, `{days}` |
| `sacca_low_attendance_alert` | Student < 75% attendance | `{name}`, `{percentage}` |
| `sacca_faculty_absent_alert` | Faculty absent 3+ days (to HR) | `{faculty_name}`, `{days}` |
| `sacca_placement_nudge` | Student not applied after drive | `{name}`, `{drive_title}` |

---

## User Roles & Permissions

| Role | Key Access |
|---|---|
| `super_admin` | Everything — all branches, all modules |
| `branch_admin` | Own branch — all modules except super admin tools |
| `hr_manager` | Attendance, leave, payroll, work reports |
| `academic_coordinator` | Timetable, student attendance, LMS, training hours |
| `faculty` | Own timetable, student attendance marking, LMS uploads |
| `student` | LMS, placement drives, own attendance/leave, complaints |
| `placement_officer` | Placement CRM, company database, drives |
| `visitor` | Read-only dashboard |

---

## License

Private — Viviz Technologies. All rights reserved.

---

*Built with [Claude Code](https://claude.ai/code) + [Laravel Boost](https://laravel.com/ai/boost)*
