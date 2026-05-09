<?php

namespace App\Providers;

use App\Models\LeaveRequest;
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
    }
}
