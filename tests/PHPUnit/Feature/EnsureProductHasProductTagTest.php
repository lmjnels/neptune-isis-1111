<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use League\Csv\Exception;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Throwable;

class EnsureProductHasProductTagTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     * @throws \Exception
     */
    public function testExample()
    {
        $products = DB::table('products');
        $products->whereNull('styles');
        $products->whereNotNull('pp_brewery_id');
        $products->orderBy('id');

        $collection = $products->get();
        $this->assertInstanceOf(Collection::class, $collection);

        if($collection->isEmpty()){
            throw new \Exception('No records matching predefined criteria to parse');
        }


        $skipped=1;

        $this->assertInstanceOf(Builder::class, $products);

        $products->chunk(100, function ($beers, $skipped) {
            $this->assertInstanceOf(Collection::class, $beers);
            foreach ($beers as $beer) {
                $this->assertIsObject($beer, 'Beer object recieved');

                $product_id = $beer->id;

                $product_tag = $collection = DB::table('product_tags');
                $collection->where('product_id', '=', $product_id);


                // product tag already exists; skip
                if (false === $collection->get()->isEmpty()) {
                    // product exists
                    $skipped++;
                    break;
                }

                $payload = json_decode(sprintf('{"SRM_scale": null, "beer_ranges": [], "beer_sights": [], "beer_smells": [], "beer_styles": [{"id": null, "name": null, "other": null, "product_id": %s}], "beer_tastes": [], "typical_ABV": null, "additional_features": []}',
                                               $product_id), true);

                DB::beginTransaction();

                try {
                    $product_tag->insert($data = [
                        'product_id'    => $product_id,
                        'product_tags'  => json_encode($payload, JSON_FORCE_OBJECT),
                        'product_allergins' => null,
                        'product_awards' => null,
                        'product_rtm' => sprintf('{"rtm": {"is_sold_directly": true,"is_white_labeled": false,"is_sold_through_wholesaler": false},"wholesalers": []}'),
                        'product_ingredients' => null,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (Exception | Throwable $e) {
                    DB::rollBack();
                    dd($e->getMessage());
                }
                DB::commit();
            }
        });
    }
}