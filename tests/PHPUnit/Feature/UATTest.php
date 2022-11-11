<?php

namespace Tests\Feature;

use App\Package\Migration\ImportProductTags;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BeerEntityTest extends TestCase
{

    public function import()
    {
        return new ImportProductTags();
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_discover()
    {
        $response = $this->post(route('api.discover'));

        dd($response);
    }

    public function test_get_search()
    {
        $response = $this->post(route('api.search'));

        dump($response);
    }
}