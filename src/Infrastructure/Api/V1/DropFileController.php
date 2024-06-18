<?php

namespace DriveManager\Infrastructure\Api\V1;

use Doctrine\ORM\EntityManagerInterface;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileInterface;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileId;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\DropFileProviderFactory;
use DriveManager\Infrastructure\Persistence\File\FileRepositoryInMemory;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DropFileController
{
    private const BASE_URI = 'https://nuage.logipro.com/owncloud/remote.php/dav/files/romain.malosse@logipro.com/';
    private string $API_KEY;
    private string $MAIL_ADDRESS;

    public function __construct(
        private FileRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
        $apiKey = getenv('API_KEY_NEXTCLOUD');
        $mailAddress = getenv('MAIL_ADDRESS');
        if ($apiKey === false) {
            throw new RuntimeException('API_KEY environment variable is not set.');
        } elseif ($mailAddress === false) {
            throw new RuntimeException('MAIL_ADDRESS environment variable is not set.');
        } else {
            $this->API_KEY = $apiKey;
            $this->MAIL_ADDRESS = $mailAddress;
        }
    }


    #[Route('/api/v1/dropFile/dropFile', 'DropFile', methods: ['POST'])]
    public function dropFile(Request $request): Response
    {
        try {
            $dropFileRequest = $this->buildDropFileRequest($request);
            $factory = new DropFileProviderFactory(self::BASE_URI, $this->MAIL_ADDRESS, $this->API_KEY);
            $service = new DropFile($factory, $this->repository);
            $service->execute($dropFileRequest);
            $this->entityManager->flush();
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
