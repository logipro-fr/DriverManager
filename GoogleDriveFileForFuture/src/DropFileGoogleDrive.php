<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\Exceptions\InvalideDataException;
use DriveManager\Application\Service\DropFile\RequestGoogleDriveApiInterface;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileName;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Safe\file_get_contents;
use function Safe\json_decode;
use function Safe\json_encode;

class DropFileGoogleDrive implements DropFileInterface
{
    public const MIME_TYPE = 'text/plain';
    private const INVALIDE_DATA_EXCEPTION = "JSON data is incomplete or invalid at %s";
    public const PATH_RESOURCES = '/tests/unit/resources/%s' ;

    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey
    ) {
    }

    /**
     * @return array{
     *     headers: array{
     *         Content-Type: string,
     *         Authorization: string
     *     },
     *     body: string
     * }
     */
    public function paramHeader(FileContent $content): array
    {
        return [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey
            ],
            'body' => $content->getContent()
        ];
    }

    public function dropFile(File $file): void
    {
        $fileMetadata = [
            'name' => $file->getFileName(),
            'mimeType' => self::MIME_TYPE,
        ];

        $body = new FileContent(json_encode([
            'metadata' => $fileMetadata,
            'data' => $file->getContent()
        ]));

        $requestParams = $this->paramHeader($body);
        $requestParams['query'] = ['uploadType' => 'media'];
        $this->client->request('POST', $file->getPath(), $requestParams);
    }

    public function isFileExists(File $file): bool
    {
        $fileExistanceTest = sprintf(getcwd() . self::PATH_RESOURCES, $file->getFileName());
        return file_exists($fileExistanceTest);
    }

    public function request(RequestGoogleDriveApiInterface $requestApi, FileName $fileName): ResponseGoogleDriveApi
    {
        /** @var array<string> $responseData */
        $responseData = $this->readFile($fileName);

        if (!isset($responseData['kind'], $responseData['id'], $responseData['name'], $responseData['mimeType'])) {
            throw new InvalideDataException(sprintf(self::INVALIDE_DATA_EXCEPTION, $requestApi::class));
        }

        return new ResponseGoogleDriveApi(
            $responseData['kind'],
            $responseData['id'],
            $responseData['name'],
            $responseData['mimeType']
        );
    }

    public function readFile(FileName $fileName): mixed
    {
        $jsonResponseFile = sprintf(getcwd() . self::PATH_RESOURCES, $fileName->getFileName());
        $jsonContent = @file_get_contents($jsonResponseFile);
        return json_decode($jsonContent, true);
    }
}
