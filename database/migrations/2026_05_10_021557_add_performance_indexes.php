<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // users: common filters
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'is_active', 'branch_id'], 'idx_users_role_active_branch');
        });

        // attendance_records: date + branch queries
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'idx_attendance_user_date');
        });

        // leave_requests: status + user
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['approver_id', 'status'], 'idx_leave_approver_status');
        });

        // tasks: deadline monitoring
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['due_date', 'status'], 'idx_tasks_due_status');
        });

        // announcements: active feed
        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['published_at', 'expires_at'], 'idx_announcements_published_expires');
        });

        // lms_materials: published by batch
        Schema::table('lms_materials', function (Blueprint $table) {
            $table->index(['faculty_id', 'is_published'], 'idx_lms_faculty_published');
        });

        // student_attendance: class date queries
        Schema::table('student_attendance', function (Blueprint $table) {
            $table->index(['enrollment_id', 'class_date'], 'idx_student_att_enrollment_date');
        });

        // daily_work_reports: status + date
        Schema::table('daily_work_reports', function (Blueprint $table) {
            $table->index(['user_id', 'report_date', 'status'], 'idx_work_reports_user_date_status');
        });

        // placement_applications: student status
        Schema::table('placement_applications', function (Blueprint $table) {
            $table->index(['student_id', 'status'], 'idx_placement_app_student_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn ($t) => $t->dropIndex('idx_users_role_active_branch'));
        Schema::table('attendance_records', fn ($t) => $t->dropIndex('idx_attendance_user_date'));
        Schema::table('leave_requests', fn ($t) => $t->dropIndex('idx_leave_approver_status'));
        Schema::table('tasks', fn ($t) => $t->dropIndex('idx_tasks_due_status'));
        Schema::table('announcements', fn ($t) => $t->dropIndex('idx_announcements_published_expires'));
        Schema::table('lms_materials', fn ($t) => $t->dropIndex('idx_lms_faculty_published'));
        Schema::table('student_attendance', fn ($t) => $t->dropIndex('idx_student_att_enrollment_date'));
        Schema::table('daily_work_reports', fn ($t) => $t->dropIndex('idx_work_reports_user_date_status'));
        Schema::table('placement_applications', fn ($t) => $t->dropIndex('idx_placement_app_student_status'));
    }
};
