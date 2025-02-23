<?php

namespace Automations\FilamentAutomations;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Automations\FilamentAutomations\Resources\FilamentAutomationResource;

class FilamentAutomationsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-automations';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            FilamentAutomationResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
