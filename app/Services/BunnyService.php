<?php

namespace App\Services;

use ToshY\BunnyNet\BunnyHttpClient;
use ToshY\BunnyNet\Enum\Endpoint;
use Symfony\Component\HttpClient\Psr18Client;
use Psr\Http\Client\ClientInterface;

class BunnyService
{
    protected $client;

    public function __construct()
    {
        $this->client = new BunnyHttpClient(
            client: new Psr18Client(),
            // For Stream API calls we must use the stream library API key
            apiKey: config('services.bunny.stream_api_key') ?: config('services.bunny.api_key'),
            baseUrl: Endpoint::STREAM,
        );
    }

    public function client()
    {
        return $this->client;
    }
}
