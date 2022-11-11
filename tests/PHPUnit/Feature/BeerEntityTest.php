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
    public function testExample()
    {
        $import = $this->import();

        $import->handle();
    }
}