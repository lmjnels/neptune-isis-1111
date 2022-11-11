<?php


namespace Foundation\Http\Client\Contract;


interface GoogleMapsApi
{
    public const REVERSE_GEOCODE = 'geocode/json?latlng=';

    public function reverse_geocode(float $latitude, float $longitude);
}
