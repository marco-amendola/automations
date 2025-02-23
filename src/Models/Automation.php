<?php

namespace Automations\FilamentAutomations\Models;

use Illuminate\Database\Eloquent\Model;
use Automations\FilamentAutomations\AutomationActions\BaseAction;

class Automation extends Model
{
    protected $table = 'filament_automations';

    protected $casts = [
        'enabled' => 'boolean',
        'trigger' => 'array',
        'actions' => 'array',
    ];

    protected $guarded = [];

    public function shouldTrigger(Model $model): bool
    {
        // All Triggers must return true
        return collect($this->trigger[0]['triggers'])->map(function ($trigger) use ($model) {
            $field = $trigger['field'];
            $operator = $trigger['operator'];
            $value = $trigger['value'];

            $modelValue = $model->{$field};

            return match ($operator) {
                '==' => $modelValue == $value,
                '===' => $modelValue === $value,
                '!=' => $modelValue != $value,
                '!==' => $modelValue !== $value,
                '>' => $modelValue > $value,
                '<' => $modelValue < $value,
                '>=' => $modelValue >= $value,
                '<=' => $modelValue <= $value,
                default => false,
            };
        })->filter(fn ($trigger) => $trigger === true)->count() === count($this->trigger[0]['triggers']);
    }

    public function runActions(Model $model): void
    {
        collect($this->actions)->each(function ($action) use ($model) {
            $actionClass = $action['action_class'];
            // /**
            //  * @var BaseAction $actionClass
            //  * */
            // $actionClass = app($actionClass);

            // $actionClass::dispatch($model);

            $has_delay = $action['delay_enabled'];
            $delay_number = (int)replaceSmartTags($model, $action['delay_number']); // 1, 2, 3, 4, 5 as values
            if ($has_delay && $delay_number > 0) {
                $delay_unit = $action['delay_unit']; //Seconds, Minutes, Hours, Days as values
                $actionInstance = new $actionClass($action, $model);
                $actionInstance::dispatch($action, $model)->delay(now()->add($delay_number, $delay_unit));
            } else {
                $actionInstance = new $actionClass($action, $model);
                $actionInstance::dispatch($action, $model);
            }

        });
    }
}
