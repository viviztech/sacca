<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', [], $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $this->log($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->getOriginal(), []);
    }

    private function log(Model $model, string $action, array $old, array $new): void
    {
        ActivityLog::create([
            'model_type' => class_basename($model),
            'model_id' => $model->getKey(),
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => $old ?: null,
            'new_values' => $new ?: null,
        ]);
    }
}
