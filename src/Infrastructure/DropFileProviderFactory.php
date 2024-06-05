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
    public function __construct(private string $baseUri, private string $mail, private string $password)
    {
    }

    public function create(string $apiName): DropFileInterface
    {
        switch ($apiName) {
            case 'NextCloud':
                return new DropFileNextcloud($this->baseUri, $this->mail, $this->password);
            case 'NextCloudMock':
                return new DropFileNextcloud($this->baseUri, $this->mail, $this->password, $this->createMock());
            case 'FileSysteme   ':
                return new DropFileForFileSystem("root");
            default:
                throw new BadApiNameException("A wrong api name is enter in paramater of function create");
        }
    }

    private function createMock(): MockHttpClient
    {
        $responses = [
            new MockResponse('', ['http_code' => 200])
        ];
        return new MockHttpClient($responses);
    }
}
