<?php


namespace App\Package\Service;

class AbstractService
{
    public function transformQueryStringArray(string $parameters): array
    {
        return explode(',', str_replace(['[',']'], '', $parameters));
    }
}