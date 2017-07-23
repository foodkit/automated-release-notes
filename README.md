## Release Note Generator

This is a Laravel package that generates markdown formatted release notes between two branches/tags.  

## Installation

Install the package via composer:

``` sh
composer require foodkit/jira-release-notes
```

Register the service provider:

```
// config/app.php

'providers' => [
    // ...
    FoodKit\ReleaseNote\Provider\ReleaseNoteServiceProvider::class,
];

```

To publish the config file to `config/release-notes.php` run:

``` sh
php artisan vendor:publish --provider="FoodKit\ReleaseNote\Provider\ReleaseNoteServiceProvider"
```

Next add the `FoodKit\ReleaseNote\Commands\GenerateReleaseNote` class to your console kernel.

```text-html-php
// app/Console/Kernel.php

protected $commands = [
   ...
    \FoodKit\ReleaseNote\Commands\GenerateReleaseNote::class,
]
```

## Usage

This command will generate the release notes between two tags.

``` sh
php artisan release-note:generate --start=v2.7.8 --end=v2.8.0
```

This  will generate the release notes between two branches.

``` sh
php artisan release-note:generate --start=develop --end=master
```