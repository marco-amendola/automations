<?php

namespace Automations\FilamentAutomations;

use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Automations\FilamentAutomations\Concerns\CanSetupAutomations;
use Automations\FilamentAutomations\Concerns\InteractsWithAutomation;
use Automations\FilamentAutomations\Observers\ModelObserver;
use Automations\FilamentAutomations\Testing\TestsFilamentAutomations;

class FilamentAutomationsServiceProvider extends PackageServiceProvider
{
    use CanSetupAutomations;

    public static string $name = 'filament-automations';

    public static string $viewNamespace = 'filament-automations';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('tschucki/filament-automations');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
    }

    public function boot(): void
    {
        parent::boot();
        $this->registerGlobalObservers();
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-automations/{$file->getFilename()}"),
                ], 'filament-automations-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentAutomations());
    }

    protected function getAssetPackageName(): ?string
    {
        return 'tschucki/filament-automations';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-automations', __DIR__ . '/../resources/dist/components/filament-automations.js'),
            Css::make('filament-automations-styles', __DIR__ . '/../resources/dist/filament-automations.css'),
            Js::make('filament-automations-scripts', __DIR__ . '/../resources/dist/filament-automations.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-automation_tables',
        ];
    }

    protected function registerGlobalObservers(): void
    {
        self::getAllModelsWithTrait(InteractsWithAutomation::class)->each(function ($model) {
            $model::observe(ModelObserver::class);
        });
    }
}
