<?php

namespace Automations\FilamentAutomations\Concerns;

use Automations\FilamentAutomations\Models\Automation;
use Automations\FilamentAutomations\Models\AutomationLog;

trait InteractsWithAutomation
{
    public function automationLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AutomationLog::class, 'model');
    }

    protected function automations(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Automation::class, 'model');
    }

    public function getAutomations()
    {
        //IF I ASSIGN A MODEL_ID TO A Automation FIRST WAS NOT WORKING FOR ME.

        //this is the edit
        if ($this->automations()->exists()) {
            return $this->automations()->get();
        } else {
            return Automation::where('model_id', null)->where('model_type', self::class)->get();
        }

        return $this->automations;
    }
    public function getModelTitelForAutomation(): string
    {
        return class_basename(self::class);
    }

    public function getTitelAttributeForAutomation()
    {
        return $this->getKey();
    }

    public function automationHasBeenSetup(): bool
    {
        $cAutomations = $this->automations();
        $automationExists = $cAutomations->exists();
        $automationIsEnabled = $this->automations()->enabled;
        $automationModelId = ! $this->automations()->model_id || $this->automation()->model_id === $this->getKey();

        return $automationExists && $automationIsEnabled && $automationModelId;
    }

    public function getTitleColumnForAutomationSearch(): ?string
    {
        return null;
    }
}
