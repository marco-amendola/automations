<?php

namespace Automations\FilamentAutomations\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Automations\FilamentAutomations\FilamentAutomations
 */
class FilamentAutomations extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Automations\FilamentAutomations\FilamentAutomations::class;
    }
    
}
