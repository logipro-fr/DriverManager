<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\Exceptions\FailUploadingFileException;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileName;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DropFileNextcloud implements DropFileInterface
{
    private const SUCCESS_STATUS_CODES = [200, 201, 204];
    private HttpClientInterface $client;

    public function __construct(
        string $baseUri,
        ?HttpClientInterface $client = null,
    ) {
        list($mailAddress,$password) = explode(" ", $_ENV["NEXTCLOUD_CREDENTIALS"]) ;

        if ($client == null) {
            $this->client = (new HttpClientFactory())->create($baseUri, $mailAddress, $password);
        } else {
            $this->client = $client;
        }
    }

    public function dropFile(File $file): void
    {
        $requestOptions = [
            'body' => $file->getContent()
        ];

        $response = $this->client->request('PUT', $file->getPath(), $requestOptions);

        if (!in_array($response->getStatusCode(), self::SUCCESS_STATUS_CODES)) {
            throw new FailUploadingFileException('Download failed : error ' . $response->getStatusCode());
        }
    }

    public function isFileExists(File $file): bool
    {
        $response = $this->client->request('HEAD', $file->getPath());

        return $response->getStatusCode() === 200;
    }

    public function readFile(File $file): string
    {

        $response = $this->client->request('GET', $file->getPath());

        return $response->getContent();
    }
}
