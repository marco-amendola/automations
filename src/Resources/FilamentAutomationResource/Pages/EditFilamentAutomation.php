<?php

namespace Automations\FilamentAutomations\Resources\FilamentAutomationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Automations\FilamentAutomations\Resources\FilamentAutomationResource;

class EditFilamentAutomation extends EditRecord
{
    protected static string $resource = FilamentAutomationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
