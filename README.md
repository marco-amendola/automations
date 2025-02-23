# Add automations to your filament app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tschucki/filament-automations.svg?style=flat-square)](https://packagist.org/packages/tschucki/filament-automations)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/automation/status/tschucki/filament-automations/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/tschucki/filament-automations/actions?query=automation%3Arun-tests+branch%3Amain)
[![Fix PHP Code Styling](https://github.com/Automations/filament-automations/actions/automations/fix-php-code-styling.yml/badge.svg)](https://github.com/Automations/filament-automations/actions/automations/fix-php-code-styling.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/tschucki/filament-automations.svg?style=flat-square)](https://packagist.org/packages/tschucki/filament-automations)

This plugin lets you add automations to your filament app. You can attach triggers and dispatchable actions to your
automations. The plugin will automatically execute the actions when the trigger conditions are met.

## Table of Contents

- [Images](#images)
- [Installation](#installation)
- [Usage](#usage)
    - [Basics](#basics)
    - [Add the trait to your model](#add-the-trait-to-your-model)
    - [Create an Action](#create-an-action)
- [Configuration](#configuration)
    - [Define searchable field](#define-searchable-field)
    - [Max Search Results](#max-search-results)
- [Testing](#testing)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security Vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

## Images

![Screenshot 1](.github/images/Basic-Form.png)
![Screenshot 2](.github/images/Trigger-Form.png)
![Screenshot 3](.github/images/Actions-Form.png)

## Installation

You can install the package via composer:

```bash
composer require tschucki/filament-automations
```

You can install the plugin using:

```bash
php artisan filament-automations:install
```

You can publish and run the migrations manually with:

```bash
php artisan vendor:publish --tag="filament-automations-migrations"
php artisan migrate
```

Register the plugin in your `AdminPanelServiceProvider`:

```php
use Automations\FilamentAutomations\FilamentAutomationsPlugin;

->plugins([
    FilamentAutomationsPlugin::make()
])
```

## Usage
### Basics
In order to let your models use automations, you need to add the `InteractsWithAutomations` trait to your model. By adding this trait, the plugin will automatically add a global observer to your model. So when ever a automation matches the event and trigger conditions, the automation will execute the actions.

### Add the trait to your model
```php
use Automations\FilamentAutomations\Concerns\InteractsWithAutomation;

class User extends Model {
  use InteractsWithAutomation;
}
```

### Create an Action
In order to attach an action to your automations, you will have to create a class within the `App\Jobs\Actions` folder. The class must extend the `BaseAction` class. This requires you to implement the `handle` method. This method will be called when the automation is executed.

The action class is very similar to a job.
When ever the action get executed, the model will be passed to the `__construct` method. You can use the model to do whatever you want.

The plugin will find this class on its own. So you don't have to register it anywhere.

```php
<?php

namespace App\Jobs\AutomationActions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Automations\FilamentAutomations\AutomationActions\BaseAction;

class TestAction extends BaseAction
{
    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        \Log::info($this->user->name . ' was created at ' . $this->user->created_at);
    }
    
    // Will be later used in the Logs (coming soon) 
    public function getActionName(): string
    {
        return 'Der Hackfleisch hassender Zerhacker';
    }

    public function getActionDescription(): string
    {
        return 'Schneidet Kopfsalat. Und das nachts :)';
    }

    public function getActionCategory(): string
    {
        return 'Default-Category';
    }

    public function getActionIcon(): string
    {
        return 'heroicon-o-adjustments';
    }
}
```

That's it. Now you can create and attach actions to your automations.

## Configuration

### Define searchable field

If you don't just want to search for the `id`, you can use the function `getTitleColumnForAutomationSearch` within your model to search in another field as well.

```php
    public function getTitleColumnForAutomationSearch(): ?string
    {
        return 'name';
    }
```

### Max Search Results
In case you want to change the max search results for the models, you can publish the config file and change the `automations.search.max_results` value (defaults to 100).
This can come in handy when you have a lot of models and the search is slow.

```php
<?php

return [
    'search' => [
        'max_results' => 100,
    ]
];
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Marcel Wagner](https://github.com/Automations)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
