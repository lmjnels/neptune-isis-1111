<?php

namespace Tests\Feature\Innovate;

use App\Models\V2\Product;
use Tests\TestCase;

class DiscoverMockTest extends TestCase
{
    public function test_can_get_discover_page_data(): void
    {
        $response = $this->get(route('api.discover'));

        // ensure response is json
        $response->assertJson($data = $response->decodeResponseJson());

        // ensure response structure of json
        $structure = [
            'handpicked_breweries',
            'suggested_products',
            'popular_breweries',
        ];

        $response->assertJsonStructure($structure, $data);

        // ensure we have a valid response
        $response->assertStatus(200);
    }

    /**
     * @throws \Exception
     */
    public function test_can_get_product_details_by_id()
    {
        $id = Product::inRandomOrder()->get()->first()->id;

        $product = $this->productService()->getProductById($id);

        $this->assertIsArray($product);

        $this->assertIsProduct($product);

        $this->assertProductId($product, $id);
    }

    /**
     * @throws \Exception
     */
    public function test_can_get_brand_details_by_id()
    {
        // brewdog
        $id = 1411;

//        $response = $this->get(route('brewery.brand-details'),['company_id' => $id]);
        $brewery = $this->breweryService()->getBreweryDetails($id);

        $this->assertIsBrewery($brewery);

        $this->assertIsProduct($brewery[0]['products']);
    }

    public function test_can_get_brand_details_by_id_from_api()
    {
        // brewdog
        $id = 1411;

        $response = $this->get(route('brewery.brand-details', ['company_id' => $id]));

        dd($response->decodeResponseJson());
    }

    /**
     * @throws \Exception
     */
    public function test_can_get_product_details_by_id_style()
    {
        //$id = Product::inRandomOrder()->get()->first()->id;

        $id =12453;

//        $product = $this->productService()->getProductsWithMatchingStyles($id);

        $response = $this->get(route('api.product', ['beer_id' => $id]));

        $product = $response->$response->decodeResponseJson();

        dd($product);

        $this->assertIsArray($product);

        $this->assertIsProduct($product);

        $this->assertProductId($product, $id);

        dump($product);
    }

    public function test_can_get_discover_search_payload()
    {
        $response = $this->get(route('api.discover.search'));


        dd($response->decodeResponseJson());
    }


    public function test_can_get_stouts_and_porters()
    {
        $response = $this->post(route('curator.english-bitters'));


        dd($response->decodeResponseJson());
    }



}
