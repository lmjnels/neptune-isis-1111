<?php

namespace Tests\Feature;

use Symfony\Component\VarDumper\VarDumper;
use Tests\TestCase;

class SandboxTest extends TestCase
{

    public function testRun()
    {
        // \App\Models\Beer\Product::with('brewery')->first()->toArray()
        $model = \App\Models\Beer\Product\Product::with('beer_product_styles');

        VarDumper::dump($model->get());

//         $model = $model->whereNotNull('styles');
//         $model = $model->where('styles.beer_id', '=', 1082);

        $model = $model->first();

//        dd($model->toSql());
        dd($model->toArray());

        return $model->toArray();
    }

    public function testGetProducts()
    {
        $service = new \App\Transformers\PintPlease\ProductService();
        $data = $service->getBeerProducts();

        dd($data);
    }

}