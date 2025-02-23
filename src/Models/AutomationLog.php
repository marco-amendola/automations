<?php

namespace Automations\FilamentAutomations\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    protected $table = 'filament_automation_logs';

    protected $guarded = [];

    public function automation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Automation::class, 'automation_id');
    }
}
