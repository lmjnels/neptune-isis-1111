<?php


namespace Tests\Api\Search;

use App\Package\Repositories\QueryBuilderRepository;
use App\Package\Service\BreweryService;
use App\Package\Service\ProductService;
use App\Repositories\QueryBuilder\BreweryRepository;
use App\Repositories\QueryBuilder\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ProductServiceMockTest extends TestCase
{

    public function test_pale_ale()
    {
        $data = $this->product()->getProductsByStyleName($style = 'Pale Ale');

        dd(collect($data)->count());
    }

    public function product()
    {
        return new ProductService($this->repository());
    }

    public function repository(){
        return new ProductRepository();
    }

    public function test_can_get_featured_products()
    {
        $test = $this->product()->getFeaturedProducts();

        dd($test);
    }

    public function test_can_get_recent_products()
    {
        $test = $this->product()->getRecentlyAddedProducts();

        dd($test);
    }

    public function test_can_get_handpicked_products()
    {
        $test = $this->product()->getHandpickedProducts();

        dd($test);
    }

    public function test_can_get_most_popular_breweries()
    {
        $test = $this->brewery()->getMostPopularProducers();

        dd($test);
    }

    public function brewery()
    {
        return new BreweryService(new BreweryRepository());
    }

    public function test_can_get_product_by_id()
    {
        $id = 50786;
        $test = $this->product()->getProductById($id);

        dd($test);
    }

    public function test_can_get_brewery_by_id()
    {
        $id = 855;
        $test = $this->brewery()->getBreweryById($id);

        dd($test);
    }


    public function test_search_endpoint()
    {
        $body = [
            'search_criteria' => [
                'product_style_id'       => [4],
                'product_style_category' => 'Pale Ale',
                'product_packaging'      => [1, 2],
                'product_style'          => [4],
                'offset'                 => 0,
                'limit'                  => 20,
            ],
        ];
        $request = $this->post(route('api.search'), $body);

        $response = $request->decodeResponseJson();

        dd($response);
    }

    public function test_discover_search()
    {
        $criteria = [
            'style_ids' => [7,54,53,52,51,50,49],
            'user' => 257,
            'page' => 1,
        ];


        $collection = $this->product()->getProductsByCriteria($criteria, 257);

        dd($collection);


        $first = $collection->whereNotNull('beer_image')->take(2);
        $rev = $collection->reverse()->take(2);

        dd($first, $rev);
    }

    public function test_curated_search()
    {
        $criteria = [
            'white_label' => true,
//            'matthew_clark' => true,
            'user' => 257,
            'page' => 1,
        ];


        $collection = $this->repository()->searchProducts($criteria, 257);

        dd($collection);


        $first = $collection->whereNotNull('beer_image')->take(2);
        $rev = $collection->reverse()->take(2);

        dd($first, $rev);
    }

    public function test_seaarch_product()
    {
        $body = [
//            'product_style_id' => [180],
        'filter' => [
        'product_style_category' => 'Pale Ale',
        'product_packaging'      => [1, 2],
        'offset'                 => 0,
        'limit'                  => 20,
        ],
        ];

        $filter = http_build_query($body['filter'], '&');
//        $explodeFilter = explode('&', $filter);

//        dd($filter, $explodeFilter);
//        dd($filter);

        $test = $this->product()->getProductsByCriteria($body, 257);

        dd($test);

        $page = 1;
        $perPage = 5;
        $pagination = new LengthAwarePaginator(
            $test,
            count($test),
            $perPage,
            $page
        );


        dd($pagination);
    }

    public function test_search_keywords()
    {
        $body = ['name' => 'Va'];
        $response = $this->post(route('search.keywords'), $body);

        dd($response->decodeResponseJson());
    }

    public function test_search_brewery()
    {
        $body = [
            'brewery' => [1411, 1173]
            //            'brewery' => [],
        ];
        $response = $this->post(route('search.brewery_products'), $body);

        dd($response->decodeResponseJson());
    }

    public function test_search()
    {
        $body = [
            'product_packaging' => '[1,3]',
        ];
        $response = $this->post(route('api.search'), $body);

        dd($response->decodeResponseJson());
    }

    public function test_search_styles()
    {
        $response = $this->post(route('search.product_style'));

        dd($response->decodeResponseJson());
    }

    public function test_get_beer_styles()
    {
        $body = ['beer_styles' => [126]];
        $response = $this->post(route('curator.sessions_beers'), $body, [
            'user'  =>  257
        ]);

        dd($response->decodeResponseJson());
    }

    public function test_get_beer_styles()
    {
        $body = ['beer_styles' => [126]];
        $response = $this->post(route('curator.sessions_beers'), $body, [
            'user'  =>  257
        ]);

        dd($response->decodeResponseJson());
    }
}
