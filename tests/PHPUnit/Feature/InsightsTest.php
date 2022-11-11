<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InsightsTest extends TestCase
{

    public function test_get_benchmarks()
    {
        $request = $this->get(route('insights.get.benchmark'));

        dd($request->decodeResponseJson());
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_store_benchmark()
    {
        $data = [
            'name'  =>  'Test_'.time(),
            'benchmarks' => [
//                1173,
//                1400,
//                245,
//                559,
//                1224,
//                1588,
//                2406,
//                155,
//                1962,
//                250,
901,1082
            ]
        ];
        $response = $this->withHeaders(['user' => 257])
            ->post(route('insights.store.benchmark', $data));

        dd($response);
    }

    public function test_auth_cumilio()
    {
        $data = [
            'name'  =>  'Test_'.time(), // is not mandatory
            'benchmarks' => [
                1173,
                1400,
                245,
                559,
                1224,
                1588,
                2406,
                155,
                1962,
                250
            ]
        ];
        $response = $this->withHeaders(['user' => 257])
            ->post(route('insights.auth.benchmark'));

        dd($response->decodeResponseJson());
    }
}
