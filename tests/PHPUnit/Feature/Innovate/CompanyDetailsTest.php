<?php

namespace Tests\Feature\Innovate;

use App\Models\V2\Company;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyDetailsTest extends TestCase
{

    public function test_can_get_company_details()
    {
//        $company_id = ['company_id' => $id = Company::inRandomOrder()->get()->first()->id];
        $company_id = ['id' => $id = 634];
        $response = $this->get(route('company.get.brand-details',$company_id));

        $payload = $response->decodeResponseJson();
        dd($payload->json());

        $this->assertArrayHasKey('brewery_name', $payload);
        $this->assertArrayHasKey('profile_summary', $payload);
        $this->assertArrayHasKey('profile_picture', $payload);
        $this->assertArrayHasKey('address', $payload);
        $this->assertArrayHasKey('location_image', $payload);
//        $this->assertArrayHasKey('feature_image', $payload);
//        $this->assertArrayHasKey('background_image', $payload);
//        $this->assertArrayHasKey('annual_capacity', $payload);
//        $this->assertArrayHasKey('accreditations', $payload);
        $this->assertArrayHasKey('youtube_url', $payload);
        $this->assertArrayHasKey('facebook_url', $payload);
        $this->assertArrayHasKey('twitter_url', $payload);
        $this->assertArrayHasKey('pintplease_url', $payload);
        $this->assertArrayHasKey('website_url', $payload);

        $this->assertEquals($id, $payload['brewery_id']);

        $response->assertStatus(200);
    }

    public function test_can_update_company_details()
    {
        $requestPayload = [
            'company_id'  => 1286,
            'brewery_name'  => 'BrewBrokers UATDEV Limited'
        ];

        $params = ['company_id' => Company::where('id', '=', 1286)->get()->first()->id];

        $response = $this->patch(route('company.patch.brand-details', $params), $requestPayload);

        $payload = $response->decodeResponseJson();

        $response->assertStatus(200);
        $this->assertEquals($requestPayload['brewery_name'], $payload['brewery_name']);
    }

    public function test_can_update_company_name(){}
    public function test_can_update_company_description(){}
    public function test_can_update_company_location(){}
    public function test_can_update_company_logo(){}
    public function test_can_update_company_background_image(){}
    public function test_can_update_company_feature_image(){}
    public function test_can_update_company_annual_capacity(){}
    public function test_can_update_company_accreditations(){}
    public function test_can_update_company_video_link(){}
    public function test_can_update_company_facebook_handle(){}
    public function test_can_update_company_twitter_handle(){}
    public function test_can_update_company_instagram_handle(){}
    public function test_can_update_company_pintplease_handle(){}
    public function test_can_update_company_website_url(){}
}
