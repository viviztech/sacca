<?php

namespace Tests\Feature;

use App\Livewire\Academic\StudentAttendanceForm;
use App\Livewire\Academic\TrainingHourDashboard;
use App\Livewire\Admin\BranchForm;
use App\Livewire\Admin\UserForm;
use App\Livewire\Announcements\AnnouncementManager;
use App\Livewire\Attendance\GeoFenceManager;
use App\Livewire\Complaints\ComplaintPortal;
use App\Livewire\Compliance\ComplianceManager;
use App\Livewire\Documents\DocumentVault;
use App\Livewire\Grooming\GroomingInspectionForm;
use App\Livewire\Leave\LeaveIndex;
use App\Livewire\Payroll\PayslipManager;
use App\Livewire\Placement\CompanyIndex;
use App\Livewire\Placement\PlacementDriveManager;
use App\Livewire\Tasks\TaskBoard;
use App\Livewire\Timetable\TimetableBuilder;
use App\Livewire\Visitors\VisitorCheckIn;
use App\Livewire\Visits\VisitPlanner;
use App\Livewire\WorkReports\DailyWorkReportPage;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AllFormsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $faculty;

    private User $student;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create(['name' => 'Test Branch', 'code' => 'TST', 'is_active' => true]);
        $this->admin = User::factory()->branchAdmin()->create(['branch_id' => $this->branch->id]);
        $this->faculty = User::factory()->faculty()->create(['branch_id' => $this->branch->id]);
        $this->student = User::factory()->student()->create(['branch_id' => $this->branch->id]);
    }

    // ── 1. Branch Form ──────────────────────────────────────────
    public function test_branch_form_saves_new_branch(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BranchForm::class)
            ->set('name', 'New Branch')
            ->set('code', 'NEW')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('branches', ['code' => 'NEW']);
    }

    public function test_branch_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(BranchForm::class)
            ->call('save')
            ->assertHasErrors(['name', 'code']);
    }

    // ── 2. User Form ────────────────────────────────────────────
    public function test_user_form_creates_new_user(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->set('name', 'New Faculty')
            ->set('email', 'newfaculty@sacca.in')
            ->set('role', 'faculty')
            ->set('branch_id', (string) $this->branch->id)
            ->set('password', 'Password@123')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'newfaculty@sacca.in']);
    }

    public function test_user_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(UserForm::class)
            ->call('save')
            ->assertHasErrors(['name', 'email', 'role']);
    }

    // ── 3. Company Index ─────────────────────────────────────────
    public function test_company_form_saves_new_company(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CompanyIndex::class)
            ->set('name', 'IndiGo Airlines')
            ->set('industry', 'Aviation')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('companies', ['name' => 'IndiGo Airlines']);
    }

    public function test_company_form_validates_name_required(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CompanyIndex::class)
            ->call('save')
            ->assertHasErrors(['name']);
    }

    // ── 4. Placement Drive Manager ──────────────────────────────
    public function test_placement_drive_form_validates_required_fields(): void
    {
        // drive_date is pre-set in mount() so it won't appear in errors
        Livewire::actingAs($this->admin)
            ->test(PlacementDriveManager::class)
            ->call('save')
            ->assertHasErrors(['company_id', 'title']);
    }

    public function test_placement_drive_form_saves(): void
    {
        $company = Company::create(['name' => 'Air India', 'is_active' => true]);

        Livewire::actingAs($this->admin)
            ->test(PlacementDriveManager::class)
            ->set('company_id', (string) $company->id)
            ->set('title', 'Ground Staff Drive 2026')
            ->set('drive_date', now()->addDays(10)->toDateString())
            ->set('positions', '5')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('placement_drives', ['title' => 'Ground Staff Drive 2026']);
    }

    // ── 5. Task Board ────────────────────────────────────────────
    public function test_task_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TaskBoard::class)
            ->call('save')
            ->assertHasErrors(['title', 'assigned_to_user_id']);
    }

    public function test_task_form_saves_new_task(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TaskBoard::class)
            ->set('title', 'Prepare lesson plan')
            ->set('assigned_to_user_id', (string) $this->faculty->id)
            ->set('priority', 'medium')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('tasks', ['title' => 'Prepare lesson plan']);
    }

    // ── 6. Announcement Manager ──────────────────────────────────
    public function test_announcement_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AnnouncementManager::class)
            ->call('save')
            ->assertHasErrors(['title', 'body']);
    }

    public function test_announcement_form_saves(): void
    {
        Livewire::actingAs($this->admin)
            ->test(AnnouncementManager::class)
            ->set('title', 'Holiday Notice')
            ->set('body', 'Institute closed tomorrow for public holiday.')
            ->set('category', 'notice')
            ->set('publish_now', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['title' => 'Holiday Notice']);
    }

    // ── 7. Complaint Portal ──────────────────────────────────────
    public function test_complaint_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->student)
            ->test(ComplaintPortal::class)
            ->call('submit')
            ->assertHasErrors(['subject', 'description']);
    }

    public function test_complaint_form_saves_anonymous(): void
    {
        Livewire::actingAs($this->student)
            ->test(ComplaintPortal::class)
            ->set('subject', 'AC not working')
            ->set('description', 'The air conditioning in classroom has been broken for 3 days.')
            ->set('category', 'facility')
            ->set('is_anonymous', true)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('complaints', ['subject' => 'AC not working', 'is_anonymous' => 1]);
    }

    // ── 8. Visitor Check-In ──────────────────────────────────────
    public function test_visitor_checkin_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(VisitorCheckIn::class)
            ->call('checkIn')
            ->assertHasErrors(['visitor_name', 'phone', 'purpose']);
    }

    public function test_visitor_checkin_saves(): void
    {
        Livewire::actingAs($this->admin)
            ->test(VisitorCheckIn::class)
            ->set('visitor_name', 'John Doe')
            ->set('phone', '9876543210')
            ->set('purpose', 'Meeting with coordinator')
            ->call('checkIn')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('visitor_logs', ['visitor_name' => 'John Doe']);
    }

    // ── 9. Geo-Fence Manager ─────────────────────────────────────
    public function test_geofence_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(GeoFenceManager::class)
            ->call('create')
            ->call('save')
            ->assertHasErrors(['branch_id', 'name', 'latitude', 'longitude']);
    }

    public function test_geofence_form_saves(): void
    {
        Livewire::actingAs($this->admin)
            ->test(GeoFenceManager::class)
            ->call('create')
            ->set('branch_id', (string) $this->branch->id)
            ->set('name', 'Main Campus')
            ->set('latitude', '11.0168')
            ->set('longitude', '76.9558')
            ->set('radius_meters', '200')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('attendance_geo_fences', ['name' => 'Main Campus']);
    }

    // ── 10. Leave Request Form ───────────────────────────────────
    public function test_leave_form_validates_required_fields(): void
    {
        Livewire::actingAs($this->student)
            ->test(LeaveIndex::class)
            ->set('showRequestForm', true)
            ->call('submitRequest')
            ->assertHasErrors(['leave_type_id', 'from_date', 'to_date', 'reason']);
    }

    // ── 11. Training Hours Form ──────────────────────────────────
    public function test_training_hours_form_validates(): void
    {
        $course = Course::create(['name' => 'Aviation', 'code' => 'AV1', 'duration_hours' => 100, 'is_active' => true]);
        $batch = Batch::create(['branch_id' => $this->branch->id, 'course_id' => $course->id, 'name' => 'Batch A', 'start_date' => today(), 'is_active' => true, 'capacity' => 30]);

        // session_type is pre-set to 'theory' in mount() so it won't appear in errors
        Livewire::actingAs($this->faculty)
            ->test(TrainingHourDashboard::class)
            ->set('showForm', true)
            ->call('save')
            ->assertHasErrors(['batch_id', 'hours_logged']);
    }

    public function test_training_hours_form_saves(): void
    {
        $course = Course::create(['name' => 'Aviation', 'code' => 'AV1', 'duration_hours' => 100, 'is_active' => true]);
        $batch = Batch::create(['branch_id' => $this->branch->id, 'course_id' => $course->id, 'name' => 'Batch A', 'start_date' => today(), 'is_active' => true, 'capacity' => 30]);

        Livewire::actingAs($this->faculty)
            ->test(TrainingHourDashboard::class)
            ->set('showForm', true)
            ->set('batch_id', (string) $batch->id)
            ->set('hours_logged', '3')
            ->set('session_type', 'theory')
            ->set('log_date', today()->toDateString())
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('training_hour_logs', ['user_id' => $this->faculty->id]);
    }

    // ── 12. Work Report Form ─────────────────────────────────────
    public function test_work_report_validates_min_length(): void
    {
        Livewire::actingAs($this->faculty)
            ->test(DailyWorkReportPage::class)
            ->set('work_summary', 'Short')
            ->call('submit')
            ->assertHasErrors(['work_summary']);
    }

    public function test_work_report_saves(): void
    {
        Livewire::actingAs($this->faculty)
            ->test(DailyWorkReportPage::class)
            ->set('work_summary', 'Completed lesson planning for batch A and reviewed student assignments.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('daily_work_reports', ['user_id' => $this->faculty->id, 'status' => 'submitted']);
    }

    // ── 13. Payroll Manager ──────────────────────────────────────
    public function test_payroll_generate_validates(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PayslipManager::class)
            ->set('month', '13')
            ->call('generate')
            ->assertHasErrors(['month']);
    }

    // ── 14. Visit Planner ────────────────────────────────────────
    public function test_visit_planner_validates_required_fields(): void
    {
        // visit_date is pre-set in mount() so it won't appear in errors
        Livewire::actingAs($this->admin)
            ->test(VisitPlanner::class)
            ->set('showForm', true)
            ->call('save')
            ->assertHasErrors(['title', 'destination', 'purpose']);
    }

    public function test_visit_planner_saves(): void
    {
        Livewire::actingAs($this->admin)
            ->test(VisitPlanner::class)
            ->set('showForm', true)
            ->set('title', 'IndiGo Airport Tour')
            ->set('visit_date', now()->addDays(5)->toDateString())
            ->set('destination', 'Coimbatore Airport')
            ->set('purpose', 'Industrial exposure visit')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('visits', ['title' => 'IndiGo Airport Tour']);
    }

    // ── 15. Grooming Inspection ──────────────────────────────────
    public function test_grooming_inspection_validates_required(): void
    {
        Livewire::actingAs($this->faculty)
            ->test(GroomingInspectionForm::class)
            ->call('save')
            ->assertHasErrors(['student_id']);
    }

    public function test_grooming_inspection_saves(): void
    {
        Livewire::actingAs($this->faculty)
            ->test(GroomingInspectionForm::class)
            ->set('student_id', (string) $this->student->id)
            ->set('inspection_date', today()->toDateString())
            ->set('uniform_ok', true)
            ->set('shoes_ok', true)
            ->set('id_card_ok', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('grooming_inspections', ['student_id' => $this->student->id]);
    }

    // ── 16. Compliance Report ────────────────────────────────────
    public function test_compliance_form_validates_required(): void
    {
        Livewire::actingAs($this->admin)
            ->test(ComplianceManager::class)
            ->set('branch_id', '')
            ->call('generate')
            ->assertHasErrors(['branch_id']);
    }

    // ── 17. Document Vault ───────────────────────────────────────
    public function test_document_vault_validates_required(): void
    {
        Livewire::actingAs($this->admin)
            ->test(DocumentVault::class)
            ->set('showForm', true)
            ->call('save')
            ->assertHasErrors(['owner_id', 'category', 'title', 'file']);
    }

    // ── 18. Timetable Builder ────────────────────────────────────
    public function test_timetable_validates_required(): void
    {
        Livewire::actingAs($this->admin)
            ->test(TimetableBuilder::class)
            ->set('showForm', true)
            ->call('save')
            ->assertHasErrors(['batch_id', 'faculty_id', 'subject', 'day_of_week', 'start_time', 'end_time']);
    }

    // ── 19. Student Attendance Form ──────────────────────────────
    public function test_student_attendance_load_validates(): void
    {
        Livewire::actingAs($this->faculty)
            ->test(StudentAttendanceForm::class)
            ->call('loadClass')
            ->assertHasErrors(['timetable_id']);
    }
}
