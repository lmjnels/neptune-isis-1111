<?php


namespace App\Package\Service;


use Illuminate\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function create(array $items, $currentPage = 1, $perPage = 24, )
    {
        $currentItems = array_slice($items, $perPage * ($currentPage - 1), $perPage);

        return new LengthAwarePaginator($currentItems, count($items), $perPage, $currentPage);
    }
}
