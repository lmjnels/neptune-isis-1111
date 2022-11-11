<?php


namespace Foundation\Http\Client;


use Foundation\Http\Client\Contract\ApiContract;
use Exception;
use Foundation\Http\Client\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

abstract class HttpClient extends ApiHandler implements ApiContract
{

    /**
     * Issue a GET request to the given URL.
     *
     * @param string $uri
     * @param array $options
     * @return \Symfony\Component\HttpFoundation\Response|PromiseInterface
     * @throws Exception
     */
    public function get(string $uri, $options = []): PromiseInterface|\Symfony\Component\HttpFoundation\Response
    {
        return $this->dispatch('GET', $uri, func_num_args() === 1 ? [] : [
            'query' => $options,
        ]);
    }

    /**
     * Issue a HEAD request to the given URL.
     *
     * @param string            $url
     * @param array|string|null $query
     *
     * @return \Foundation\Http\Client\Response
     * @throws \Exception
     */
    public function head(string $url, $query = null)
    {
        return $this->dispatch('HEAD', $url, func_num_args() === 1 ? [] : [
            'query' => $query,
        ]);
    }

    /**
     * Issue a POST request to the given URL.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Foundation\Http\Client\Response
     * @throws \Exception
     */
    public function post(string $url, array $data = [])
    {
        return $this->dispatch('POST', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PATCH request to the given URL.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Foundation\Http\Client\Response
     * @throws \Exception
     */
    public function patch($url, $data = [])
    {
        return $this->dispatch('PATCH', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a PUT request to the given URL.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Foundation\Http\Client\Response
     * @throws \Exception
     */
    public function put($url, $data = [])
    {
        return $this->dispatch('PUT', $url, [
            $this->bodyFormat => $data,
        ]);
    }

    /**
     * Issue a DELETE request to the given URL.
     *
     * @param string $url
     * @param array  $data
     *
     * @return \Foundation\Http\Client\Response|\GuzzleHttp\Promise\PromiseInterface
     * @throws \Exception
     */
    public function delete($url, $data = [])
    {
        return $this->dispatch('DELETE', $url, empty($data) ? [] : [
            $this->bodyFormat => $data,
        ]);
    }

}
