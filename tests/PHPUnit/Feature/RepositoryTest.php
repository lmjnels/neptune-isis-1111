<?php

namespace Tests\Feature;

use App\Repositories\Eloquent\QueryBuilder\ProductRepository;
use Illuminate\Support\Collection;
use Tests\TestCase;

class RepositoryTest extends TestCase
{

    public function productRepository()
    {
        return new ProductRepository();
    }

    public function testRun()
    {
        $this->test_can_get_products_by_highest_rating();

        $this->test_can_get_products_by_brew_ldn_exhibitor();
    }

    public function test_can_get_product_like_name()
    {
        $product = $this->productRepository();

        $product = $product->getProductLikeName('test');

        $this->assertInstanceOf(Collection::class, $product);
    }

    public function test_can_get_products_by_highest_rating()
    {
        $product = $this->productRepository();

        $rating = $product->getHighestRatedBeerProducts();

        // @todo take 10 arrays and make sure the rating is descending
        $this->assertInstanceOf(Collection::class, $rating);

        return $rating;
    }

    public function test_can_get_products_by_brew_ldn_exhibitor()
    {
        $product = $this->productRepository();

        $rating = $product->getBrewLdnExhibitorProducts();

        // @todo make sure column `is_brewldn_exhibitor` is true
        $this->assertInstanceOf(Collection::class, $rating);

        return $rating;
    }

    public function test_can_get_products_by_abv()
    {
        $zeroPointOne = $this->productRepository()->getAbvBelowZeroPointOne();
        $onePointThree = $this->productRepository()->getAbvBelowOnePointThree();
        $threePointFive = $this->productRepository()->getAbvBelowThreePointFive();
        $fivePointSeven = $this->productRepository()->getAbvBelowFivePointSeven();
        $seventAndAbove = $this->productRepository()->getAbvAboveSeven();

        $this->assertInstanceOf(Collection::class, $zeroPointOne);
        $this->assertInstanceOf(Collection::class, $onePointThree);
        $this->assertInstanceOf(Collection::class, $threePointFive);
        $this->assertInstanceOf(Collection::class, $fivePointSeven);
        $this->assertInstanceOf(Collection::class, $seventAndAbove);
    }

}