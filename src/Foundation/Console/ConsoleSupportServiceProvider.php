<?php

namespace Foundation\Console;


class ConsoleSupportServiceProvider extends \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider
{
    /**
     * The provider class names.
     *
     * @var array
     */
    protected $providers = [
        \Foundation\Console\Artisan\ArtisanServiceProvider::class,
        \Illuminate\Database\MigrationServiceProvider::class,
        \Illuminate\Foundation\Providers\ComposerServiceProvider::class,
        \Foundation\Repository\RepositoryServiceProvider::class,
    ];
}
