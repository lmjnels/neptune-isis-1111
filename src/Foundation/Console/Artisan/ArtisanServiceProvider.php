<?php

namespace Foundation\Console\Artisan;

final class ArtisanServiceProvider extends \Illuminate\Foundation\Providers\ArtisanServiceProvider
{

    private array $customCommands = [
        'ModelMake' => ModelMakeCommand::class,
        'RequestMake' => RequestMakeCommand::class,
        'TestMake' => TestMakeCommand::class,
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    final public function register(): void
    {
        $this->registerCommands(array_merge(
            $this->commands, $this->devCommands, $this->customCommands
        ));
    }

    /**
     * Register the command.
     *
     * @return void
     */
    final protected function registerTestMakeCommand(): void
    {
        $this->app->singleton('command.test.make', function ($app) {
            return new TestMakeCommand($app['files']);
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    final protected function registerModelMakeCommand()
    {
        $this->app->singleton(ModelMakeCommand::class, function ($app) {
            return new ModelMakeCommand($app['files']);
        });
    }

    protected function registerRepositoryMakeCommand(): void
    {
        $this->app->singleton(MakeRepositoryCommand::class, function ($app) {
            return new MakeRepositoryCommand($app['files']);
        });
    }

    protected function registerServiceMakeCommand(): void
    {

    }
}
