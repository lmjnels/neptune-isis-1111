<?php

namespace Foundation\Console\Artisan;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'make:request')]
class RequestMakeCommand extends \Illuminate\Foundation\Console\RequestMakeCommand
{
    use MakeTrait;
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    final protected function getStub()
    {
        return $this->getStubDirectory().'request.stub';
    }
}
