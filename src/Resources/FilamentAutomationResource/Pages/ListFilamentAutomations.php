<?php

namespace Automations\FilamentAutomations\Resources\FilamentAutomationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Automations\FilamentAutomations\Resources\FilamentAutomationResource;

class ListFilamentAutomations extends ListRecords
{
    protected static string $resource = FilamentAutomationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
