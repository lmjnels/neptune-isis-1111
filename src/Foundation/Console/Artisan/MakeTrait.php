<?php


namespace Foundation\Console\Artisan;


trait MakeTrait
{
    protected function getStubDirectory(): string
    {
        $root = dirname(app_path(), 1);

        $path = $root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Foundation';

        return $path.DIRECTORY_SEPARATOR.'stubs'.DIRECTORY_SEPARATOR;
    }
}
