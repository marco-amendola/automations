<?php

namespace Automations\FilamentAutomations\AutomationActions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Filament\Forms;
use Filament\Forms\Components\Component;

class BaseAction implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;


    public function handle(): void
    {
        //
    }

    public function getActionName(): string
    {
        return '';
    }

    public function getActionDescription(): string
    {
        return '';
    }

    public function getActionCategory(): string
    {
        return '';
    }

    public function getActionIcon(): string
    {
        return 'heroicon-o-forward';
    }


}
