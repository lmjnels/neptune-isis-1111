<?php


namespace App\Services\PintPlease;


use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ConsumeBreweryStats
{
    private string $endpoint = 'https://analytics.pintplease.com/brewbroker/breweryStats';

    public function get()
    {
        if (null === $this->endpoint) {
            throw new InvalidArgumentException(' must be set');
        }

        return $this->endpoint;
    }


    /**
     * @param string|null $uri
     *
     * @return \Illuminate\Http\Client\Response
     */
    public static function fetch(?string $uri = null)
    {
        $self = new self();
        $url = ($uri) ? $self->get() . '/' . $uri : $self->get();

        $response = Http::withHeaders(['X-Secret' => 'KrkjPg5a366I2NE!*NQ7WQB7*$@%VL'])->get($url);

        return $response;
    }
}
