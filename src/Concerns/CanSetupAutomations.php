<?php

namespace Automations\FilamentAutomations\Concerns;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Automations\FilamentAutomations\AutomationActions\BaseAction;

use Filament\Forms\Components\Component;

trait CanSetupAutomations
{
    public static function getAllModels(): Collection
    {
        $models = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();

                return sprintf(
                    '\%s%s',
                    Container::getInstance()->getNamespace(),
                    str_replace('/', '\\', substr($path, 0, strrpos($path, '.')))
                );
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) &&
                        !$reflection->isAbstract();
                }

                return $valid;
            })->map(function ($class) {
                return app($class);
            });

        return $models->values();
    }

    public static function getAllActions(): Collection
    {
        $actions = collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();

                return sprintf(
                    '\%s%s',
                    Container::getInstance()->getNamespace(),
                    str_replace('/', '\\', substr($path, 0, strrpos($path, '.')))
                );
            })
            ->filter(function ($class) {
                $valid = false;

                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(BaseAction::class) &&
                        !$reflection->isAbstract();
                }

                return $valid;
            })->map(function ($class) {
                return app($class);
            });

        return $actions->values();
    }

    public static function getAllModelsWithTrait(string $trait): Collection
    {
        return self::getAllModels()->filter(fn ($model) => self::modelUsesTrait($model, $trait));
    }

    public static function modelUsesTrait(Model $model, string $trait): bool
    {
        return array_key_exists($trait, (new \ReflectionClass($model))->getTraits());
    }

    public static function getModelTypeOptions()
    {
        return self::getAllModelsWithTrait(InteractsWithAutomation::class)->mapWithKeys(function ($model, $key) {
            return [
                $model::class => $model->getModelTitelForAutomation(),
            ];
        });
    }

    public static function getActionOptions()
    {
        return self::getAllActions()->mapWithKeys(function (BaseAction $action, $key) {
            return [
                $action::class => $action->getActionName(),
            ];
        });
    }

    //get form by action class
    public static function getActionFormByClass($actionClass)
    {
        $action = self::getAllActions()->first(fn ($action) => $action::class == $actionClass);

        if ($action) {
            return $action->getActionForm();
        }

        return [];
    }


    public static function getModelOptions(?Model $model, string $search = ''): array
    {
        if (!$model) {
            return [];
        }

        if (self::modelUsesTrait($model, InteractsWithAutomation::class)) {
            $iMaxResults = config('automations.search.results', 100);
            $searchableColumn = $model->getTitleColumnForAutomationSearch();

            if (!empty($search)) {
                return $model::where('id', 'LIKE', '%' . $search . '%')->when($searchableColumn !== null, fn (Builder $query) => $query->orWhere($searchableColumn, 'LIKE', '%' . $search . '%'))->take($iMaxResults)->get()->mapWithKeys(function ($model, $key) {
                    return [
                        $model->getKey() => $model->getTitelAttributeForAutomation(),
                    ];
                })->toArray();
            }

            return $model::take($iMaxResults)->get()->mapWithKeys(function ($model) {
                return [
                    $model->getKey() => $model->getTitelAttributeForAutomation(),
                ];
            });
        }

        throw new \InvalidArgumentException(sprintf('Model %s does not use trait %s', $model::class, InteractsWithAutomation::class));
    }

    public static function getModelFields(?Model $model): array
    {
        if (!$model) {
            return [];
        }

        if (self::modelUsesTrait($model, InteractsWithAutomation::class)) {
            $table = $model->getTable();

            return collect(Schema::getColumnListing($table))->mapWithKeys(function ($column) {
                return [
                    $column => $column,
                ];
            })->toArray();
        }

        throw new \InvalidArgumentException(sprintf('Model %s does not use trait %s', $model::class, InteractsWithAutomation::class));
    }
}