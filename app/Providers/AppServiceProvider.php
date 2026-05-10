<?php

namespace App\Providers;

use App\Models\Complaint;
use App\Models\LeaveRequest;
use App\Models\PlacementDrive;
use App\Models\Task;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Relation::morphMap([
            'leave_request' => LeaveRequest::class,
        ]);

        // Activity log observers — key models only to avoid excessive logging
        User::observe(ActivityLogObserver::class);
        LeaveRequest::observe(ActivityLogObserver::class);
        Task::observe(ActivityLogObserver::class);
        Complaint::observe(ActivityLogObserver::class);
        PlacementDrive::observe(ActivityLogObserver::class);
    }
}
