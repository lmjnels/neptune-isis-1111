<?php


namespace Foundation\Http\Client;


use Foundation\Http\Client\Contract\GoogleMapsApi;

final class GoogleMapsClient implements GoogleMapsApi
{
    /**
     * @var \Foundation\Http\Client\ApiClient $api
     */
    private $api;

    public function __construct() {
        $config = config('client.google_maps');
        $this->api = new HttpClient($config);

    }

    public function reverse_geocode(float $latitude, float $longitude)
    {
        return $this->api->get(sprintf(GoogleMapsApi::REVERSE_GEOCODE.'%s,%s&key=%s', $latitude, $longitude, $this->getSecretKey()));
    }

    private function getSecretKey(): string
    {
        return env('GOOGLE_MAPS_SECRET');
    }
}
