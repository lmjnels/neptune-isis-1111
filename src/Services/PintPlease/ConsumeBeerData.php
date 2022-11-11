<?php


namespace App\Services\PintPlease;


use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class ConsumeBeerData
{
    private string $ = 'https://api.pintplease.net/brewbroker/v1/updatedBeers';

    public function get()
    {
        if (null === $this->) {
            throw new InvalidArgumentException(' must be set');
        }

        return $this->;
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

        $response = Http::withBasicAuth('pintplease-collab@brewbroker.com', 'pRfCUds3@Zv4Lw@rN%9c70epzSiW46')->get($url);

        return $response;
    }
}
