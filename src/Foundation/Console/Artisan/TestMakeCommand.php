<?php

namespace Foundation\Console\Artisan;

use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:test')]
final class TestMakeCommand extends \Illuminate\Foundation\Console\TestMakeCommand
{
    use MakeTrait;

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    final protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->option('unit')) {
            return $rootNamespace.'\Unit';
        }

        return $rootNamespace.'\Feature';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    final protected function rootNamespace(): string
    {
        return 'Tests\PHPUnit';
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

        return base_path('tests/PHPUnit').str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    final protected function getStub(): string
    {
        $path = dirname(__DIR__,2);

        if ($this->option('unit')) {
            return $this->getStubDirectory().'unit-test.stub';
        }

        return $this->getStubDirectory().'test.stub';
    }
}
