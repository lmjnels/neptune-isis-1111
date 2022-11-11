<?php

namespace Tests\Feature;

use App\Models\V2\Product;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\VarDumper\VarDumper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateProductWithCompanyRelationshipTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     * @throws \Exception
     */
    public function testRun()
    {
        /** @var Collection $products */
        $products = $this->getProductsWithNullableCompany();

        /** @var Collection $batch */
        /** @var Collection $product */
        foreach ($products->chunk(100) as $k => $batch){
            foreach($batch->chunk(1) as $product){
                $pp_brewery_id = $this->getPintPleaseBreweryIdFromProduct($product);

                if(false === $brewery = $this->getCompanyByPintPleadId($pp_brewery_id)){
                    // Log brewery name and all id's (bb, pp,cc)
                    break;
                }

                $this->updateProductWithCompanyId(collect($product->first()), $brewery);

            }
        }



        $company = $this->getCompanyByPintPleadId($pp_brewery_id);

        // update product with company_id
    }
    /**
     * @return \Illuminate\Support\Collection
     * @throws \Exception
     */
    public function getProductsWithNullableCompany(): Collection
    {
        $products = DB::table('products')->whereNull('company_id')->whereNotNull('pp_brewery_id');

        $collection = $products->get(['id', 'company_id', 'pp_brewery_id', 'pp_product_id']);

        if ($collection->isEmpty()) {
            throw new Exception('No available products with fixable Pint Please brewery id\'s');
        }


        return $collection;
    }

    public function getPintPleaseBreweryIdFromProduct(Collection $product){
        if($product->isEmpty()){
            throw new Exception('Product collection is empty');
        }

        if(false === collect($product->first())->has('pp_brewery_id')) {
            throw new Exception('pp_brewery_id cannot be found in Collection');
        }

        return $product->first()->pp_brewery_id;
    }
    public function getCompanyByPintPleadId($pp_brewery_id){
        $breweries = DB::table('companies')->where('pp_brewery_id', '=', $pp_brewery_id);

        $collection = $breweries->get();

        if(false === $collection->isNotEmpty()){
            return false;
        }

        return collect($collection->first());
    }

    public function updateProductWithCompanyId(Collection $product, Collection $brewery){
        $product = DB::table('products')->where('id', '=', $product_id = $product->get('id'));

        $collection = $product->get();

        if($collection->isEmpty()){
            throw new Exception(sprintf('Could not find legacy product id: %s', $product_id));
        }

        $data = ['company_id' => $brewery->get('id'), 'status' => 1];

        $product->update($data);
    }
}