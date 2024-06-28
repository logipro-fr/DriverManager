<?php

namespace DriveManager\Infrastructure\DriveProvider;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\HttpClient;

class HttpClientFactory
{
    public function create(string $baseUri, string $username, string $password): HttpClientInterface
    {
        return HttpClient::create([
            'base_uri' => $baseUri,
            'auth_basic' => [$username, $password],
            'headers' => [
                'OCS-APIRequest' => 'true',
                'Accept' => 'application/json',
            ],
        ]);
    }
}
