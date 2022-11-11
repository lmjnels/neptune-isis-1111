<?php

namespace Tests\Feature\Innovate;

use App\Models\V2\Product;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscoveryTest extends TestCase
{
    /**
     * Discover endpoint test.
     */
    public function test_can_get_discovery_data(): void
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
     * Discover search endpoint test.
     */
    public function test_can_get_discovery_search_data(): void
    {
        $response = $this->get(route('api.discover.search'));

        // ensure response is json
        $response->assertJson($data = $response->decodeResponseJson());

        // ensure response structure of json
        $structure = [
            'recently_added_products',
            'recently_launched_products',
            'handpicked_products',
            'suggested_products',
            'popular_breweries',
            'curated',
            'insights',
            'discover',
        ];

        $response->assertJsonStructure($structure, $data);

        // ensure we have a valid response
        $response->assertStatus(200);
    }

    /**
     * Get product details endpoint test.
     */
    public function test_can_get_product_details_by_id()
    {
        $id = Product::inRandomOrder()->get()->first()->id;

        $response = $this->get(route('api.product', ['beer_id' => $id]));

        // ensure response is json
        $response->assertJson($product = $response->decodeResponseJson());

        // ensure response structure of json
        $structure = [
            'brewery_name',
            'brewery_description',
            'brewery_image',
            'beer_id',
            'beer_name',
            'beer_description',
            'beer_image',
            'beer_style',
            'beer_abv',
            'beer_rating',
            'favourite',
            'beer_styles',
        ];

        $response->assertJsonStructure($structure, $product[0]);

        $this->assertIsArray($product);

        $this->assertIsProduct($product);

        $this->assertProductId($product, $id);
    }

    /**
     * Get company details endpoint test.
     */
    public function test_can_get_company_details_by_id()
    {
        // brewdog
        $id = 1411;

        $response = $this->get(route('brewery.brand-details', ['company_id' => $id]));

        // ensure response is json
        $response->assertJson($brewery = $response->decodeResponseJson());

        $this->assertIsBrewery($brewery);

        $this->assertIsProduct($brewery['products']);
    }

    public function test_can_discover_styles()
    {
        $response = $this->get(route('discover.styles'));

        $styles = json_decode($response->decodeResponseJson()->json);

        // ensure response is json
        $response->assertJson($styles);


        $this->assertArrayHasKey('id', $styles[0]);
        $this->assertArrayHasKey('name', $styles[0]);
        $this->assertArrayHasKey('styles', $styles[0]);
    }

    public function test_can_get_discover_styles_by_id()
    {
        $style_ids =[51, 43];

        $response = $this->get(route('discover.styles', ['styles' => $style_ids]));

        // ensure response is json
        $response->assertJson($styles = $response->decodeResponseJson());

        $this->assertIsArray($styles);

        $this->assertArrayHasKey('count', $styles);
    }
}
