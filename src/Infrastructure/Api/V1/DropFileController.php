<?php

namespace DriveManager\Infrastructure\Api\V1;

use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\DropFileProviderFactory;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use function Safe\file_get_contents;

class DropFileController
{
    private const PATH_RESOURCES = '/tests/unit/resources/%s';
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/';
    private const MAIL_ADDRESS = 'romain.malosse@logipro.com';
    private string $apiKey;

    public function __construct(
        //private DropFileInterface $dropFileInterface,
        private FileRepositoryInterface $repository
    ) {
        $this->apiKey = file_get_contents(getcwd() . sprintf(self::PATH_RESOURCES, 'NextCloudApiKey.txt'));
    }

    #[Route('/api/v1/dropFile/dropFile', 'DropFile', methods: ['POST'])]
    public function dropFile(Request $request): Response
    {
        $dropFileRequest = $this->buildDropFileRequest($request);
        $factory = new DropFileProviderFactory(self::BASE_URI, self::MAIL_ADDRESS, $this->apiKey);
        $service = new DropFile($factory, $this->repository);

        try {
            $service->execute($dropFileRequest);
        } catch (Exception $e) {
            $className = (new \ReflectionClass($e))->getShortName();
            return new JsonResponse(
                [
                    'success' => false,
                    'ErrorCode' => $className,
                    'data' => '',
                    'message' => $e->getMessage(),
                ],
                400
            );
        }

        $response = $service->getResponse();
        return new JsonResponse(
            [
                'success' => true,
                'ErrorCode' => "",
                'data' => ['fileId' =>  $response->createdFileId],
                'message' => "",
            ],
            201
        );
    }

    private function buildDropFileRequest(Request $request): DropFileRequest
    {
        $content = $request->getContent();
        /** @var array<string> $data */
        $data = json_decode($content, true);

        /** @var string */
        $fileId = $data['fileId'];
        /** @var string */
        $fileToDeposit = $data['fileToDeposit'];
        /** @var string */
        $filePathToDirectory = $data['filePathToDirectory'];
        /** @var string */
        $fileDate = $data['fileDate'];
        /** @var string */
        $fileContent = $data['fileContent'];
        /** @var string */
        $driver = $data['driver'];

        return new DropFileRequest(
            $fileId,
            $fileToDeposit,
            $filePathToDirectory,
            $fileDate,
            $fileContent,
            $driver
        );
    }
}
