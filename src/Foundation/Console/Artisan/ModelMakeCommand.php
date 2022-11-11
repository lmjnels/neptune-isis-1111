<?php

namespace Foundation\Console\Artisan;

use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:model')]
final class ModelMakeCommand extends \Illuminate\Foundation\Console\ModelMakeCommand
{

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    final protected function rootNamespace(): string
    {
        return 'App\Models';
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    final protected function getPath($name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path('app/Models').str_replace('\\', '/', $name).'.php';
    }
}
