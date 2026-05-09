<?php

use App\Livewire\Admin\BranchForm;
use App\Livewire\Admin\BranchIndex;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

// Authenticated web app
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Placeholder routes for sidebar links (to be implemented in later phases)
    Route::get('/attendance', fn () => 'Attendance — Phase 2')->name('attendance.index');
    Route::get('/leaves', fn () => 'Leave — Phase 2')->name('leaves.index');
    Route::get('/timetable', fn () => 'Timetable — Phase 3')->name('timetable.index');
    Route::get('/lms', fn () => 'LMS — Phase 3')->name('lms.index');
    Route::get('/placement', fn () => 'Placement — Phase 4')->name('placement.index');
    Route::get('/announcements', fn () => 'Announcements — Phase 5')->name('announcements.index');
    Route::get('/complaints', fn () => 'Complaints — Phase 5')->name('complaints.index');

    // Super Admin + Branch Admin
    Route::middleware('role:super_admin,branch_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/branches', BranchIndex::class)->name('branches.index');
        Route::get('/branches/create', BranchForm::class)->name('branches.create');
        Route::get('/branches/{branch}/edit', BranchForm::class)->name('branches.edit');

        Route::get('/users', UserIndex::class)->name('users.index');
        Route::get('/users/create', UserForm::class)->name('users.create');
        Route::get('/users/{user}/edit', UserForm::class)->name('users.edit');
    });
});
