<?php

namespace Foundation\Http\Client\Contract;

interface ApiContract
{
    public function get(string $url, array $data = []);

    public function post(string $url, array $data = []);

    public function put(string $url, array $data = []);

    public function delete(string $url, array $data = []);
}
