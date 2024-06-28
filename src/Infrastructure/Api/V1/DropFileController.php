<?php

namespace DriveManager\Infrastructure\Api\V1;

use Doctrine\ORM\EntityManagerInterface;
use DriveManager\Application\Service\DropFile\DropFile;
use DriveManager\Application\Service\DropFile\DropFileRequest;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Infrastructure\DropFileProviderFactory;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DropFileController
{
    // Constantes pour les codes de statut HTTP
    private const HTTP_CREATED = 201;
    private const HTTP_BAD_REQUEST = 400;

    public function __construct(
        private FileRepositoryInterface $repository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/api/v1/dropFile/dropFile', 'DropFile', methods: ['POST'])]
    public function dropFile(Request $request): Response
    {
        try {
            $dropFileRequest = $this->buildDropFileRequest($request);
            $factory = new DropFileProviderFactory($_ENV["NEXTCLOUD_SERVER"]);
            $service = new DropFile($factory, $this->repository);
            $service->execute($dropFileRequest);
            $this->entityManager->flush();

            $response = $service->getResponse();
            return $this->createJsonResponse(true, '', ['fileId' => $response->createdFileId], '', self::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->createJsonResponse(
                false,
                (new \ReflectionClass($e))->getShortName(),
                [],
                $e->getMessage(),
                self::HTTP_BAD_REQUEST
            );
        }
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
        $fileContent = $data['fileContent'];
        /** @var string */
        $driver = $data['driver'];

        return new DropFileRequest(
            $fileId,
            $fileToDeposit,
            $filePathToDirectory,
            $fileContent,
            $driver
        );
    }

    /**
     * @param array<string, mixed> $data
    */
    private function createJsonResponse(
        bool $success,
        string $errorCode,
        array $data,
        string $message,
        int $statusCode
    ): JsonResponse {
        $response = [
            'success' => $success,
            'statusCode' => $errorCode,
            'data' => $data,
            'message' => $message,
        ];

        return new JsonResponse($response, $statusCode);
    }
}
