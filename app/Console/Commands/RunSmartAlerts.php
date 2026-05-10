<?php

namespace App\Console\Commands;

use App\Modules\Alerts\Services\SmartAlertService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alerts:run-smart-checks')]
#[Description('Run all AI smart alert rules and dispatch notifications')]
class RunSmartAlerts extends Command
{
    public function handle(SmartAlertService $service): int
    {
        $results = $service->runAll();
        foreach ($results as $rule => $count) {
            $this->line("  {$rule}: {$count} alerts dispatched");
        }
        $this->info('Smart alert check complete.');

        return self::SUCCESS;
    }
}
