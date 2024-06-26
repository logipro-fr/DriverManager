<?php

namespace DriveManager\Infrastructure;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\ProviderAbstractFactory;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use DriveManager\Infrastructure\Exceptions\BadApiNameException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class DropFileProviderFactory extends ProviderAbstractFactory
{
    public function __construct(private string $baseUri)
    {
    }

    public function create(string $apiName): DropFileInterface
    {
        switch ($apiName) {
            case 'NextCloud':
                return new DropFileNextcloud($this->baseUri);
            case 'NextCloudMock':
                return new DropFileNextcloud($this->baseUri, $this->createMock());
            case 'FileSystem':
                return new DropFileForFileSystem();
            default:
                throw new BadApiNameException("A wrong api name is enter in paramater of function create");
        }
    }

    private function createMock(): MockHttpClient
    {
        $responses = [
            new MockResponse()
        ];
        return new MockHttpClient($responses);
    }
}
