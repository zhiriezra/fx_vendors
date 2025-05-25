<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait MakesHttpRequests
{
    /**
     * Sends a POST request and returns the Response object.
     *
     * @param string $url
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     * @throws \RuntimeException
     */
    protected function post(string $url, array $data)
    {
        return $this->sendRequest('post', $url, $data);
    }

    /**
     * Sends a GET request and returns the Response object.
     *
     * @param string $url
     * @param array $query
     * @return \Illuminate\Http\Client\Response
     * @throws \RuntimeException
     */
    protected function get(string $url, array $query = [])
    {
        return $this->sendRequest('get', $url, $query);
    }

    /**
     * Sends an HTTP request and returns the Response object.
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     * @throws \RuntimeException
     */
    private function sendRequest(string $method, string $url, array $data)
    {
        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::$method($url, $data);

        if ($response->failed()) {
            Log::error('HTTP Request failed:', ['method' => $method, 'url' => $url, 'response' => $response->body()]);
            throw new \RuntimeException(ucfirst($method) . ' request failed: ' . $response->body());
        }

        return $response; // Return the raw Response object
    }
}
