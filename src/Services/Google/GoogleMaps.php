<?php


namespace App\Package\Service\Google;


class GoogleMaps
{
    /**
     * Map image from Google Maps
     *
     * @param $address
     *
     * @return string
     */
    public static function getMapImage($address): string
    {
        $url = 'https://maps.googleapis.com/maps/api/staticmap?';

        $query_params = [
            'center' => $address,
            'zoom'  =>  15,
            'scale' => 1,
            'size' => '600x600',
            'maptype' => 'roadmap',
            'key'   =>  env('GOOGLE_MAPS_API_KEY'),
            'format'   =>  'png',
            'visual_refresh'   =>  'true',
        ];

        return $url . http_build_query($query_params);
    }

    /**
     * Map with link to Google Maps
     *
     * @param $address
     *
     * @return string
     */
    public static function getMapUrl($address): string
    {
        $url = 'https://www.google.com/maps/place/';

        return $url . urlencode(trim($address));
    }
}
