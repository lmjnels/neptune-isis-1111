<?php


namespace App\Services\PintPlease;


use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ConsumeBreweryData
{
    private string $endpoint = 'https://api.pintplease.net/brewbroker/v1/updatedBreweries';

    public function getEndpoint()
    {
        if (null === $this->endpoint) {
            throw new InvalidArgumentException('Endpoint must be set');
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
        $url = ($uri) ? $self->getEndpoint() . '/' . $uri : $self->getEndpoint();

        $response = Http::withBasicAuth('pintplease-collab@brewbroker.com', 'pRfCUds3@Zv4Lw@rN%9c70epzSiW46')->get($url);

        return $response;
    }
}
