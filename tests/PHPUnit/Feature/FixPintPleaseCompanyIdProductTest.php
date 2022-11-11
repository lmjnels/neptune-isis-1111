<?php

namespace Tests\Feature;

use App\Models\V2\Company;
use App\Models\V2\Product;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FixPintPleaseCompanyIdProductTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $products = DB::table('products');
        $products->whereNull('company_id');
        $products->whereNotNull('pp_brewery_id');
        $products->orderBy('id');
        // $products->where('pp_brewery_id', '!=', null);

        $products = $products->chunk(1, function($beer)
        {
            $brewery_id = $beer->first()->pp_brewery_id;

            $company = DB::table('companies');
            $company->where('pp_brewery_id', '=', $brewery_id);


            dd($company->get());


        });
    }
}