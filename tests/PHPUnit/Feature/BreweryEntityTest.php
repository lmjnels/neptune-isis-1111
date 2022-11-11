<?php

namespace Tests\Feature;

use App\Models\V2\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreweryEntityTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
//        $breweries = Company::with('product')->get();

        $breweries = Company::with('product')->where('id', '=', 5)->get();

        dd($breweries->toArray());
    }
}