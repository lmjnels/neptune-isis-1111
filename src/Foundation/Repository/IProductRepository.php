<?php


namespace Foundation\Repository;


use Illuminate\Support\Collection;

interface IProductRepository
{
    public function getProductLikeName($beer_name);

    public function getHighestRatedBeerProducts();

    public function getBrewLdnExhibitorProducts();

}
