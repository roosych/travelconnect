<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class ApiClient
{
    private string $baseUrl;
    private ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('app.api_base_url', config('app.url') . '/api'), '/');
        $this->token = Session::get('api_token');
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $http = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(15);

        if ($this->token) {
            $http = $http->withToken($this->token);
        }

        return $http;
    }

    public function get(string $path, array $query = []): array
    {
        $response = $this->client()->get($path, $query);
        return $this->decode($response);
    }

    public function post(string $path, array $data = []): array
    {
        $response = $this->client()->post($path, $data);
        return $this->decode($response);
    }

    public function patch(string $path, array $data = []): array
    {
        $response = $this->client()->patch($path, $data);
        return $this->decode($response);
    }

    public function delete(string $path): array
    {
        $response = $this->client()->delete($path);
        return $this->decode($response);
    }

    private function decode(Response $response): array
    {
        $body = $response->json() ?? [];

        if (!isset($body['success'])) {
            $body['success'] = $response->successful();
        }

        return $body;
    }
}
