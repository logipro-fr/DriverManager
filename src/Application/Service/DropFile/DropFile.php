<?php

namespace DriveManager\Application\Service\DropFile;

use DriveManager\Application\Service\ProviderAbstractFactory;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Domain\Model\File\Path;

class DropFile
{
    private DropFileResponse $dropFileResponse;

    public function __construct(
        private ProviderAbstractFactory $dropfileProviderfactory,
        private FileRepositoryInterface $repository
    ) {
    }

    public function execute(DropFileRequest $request): void
    {
        $dropFileApi = $this->dropfileProviderfactory->create($request->apiName);

        $fileName = new FileName($request->fileToDeposit);
        $fullPath = $request->filePathToDirectory . $request->fileToDeposit;
        $path = new Path($fullPath);
        $fileContent = new FileContent($request->fileContent);
        $file = new File($fileName, $path, $fileContent);
        $this->repository->add($file);
        $dropFileApi->dropFile($file);

        $this->dropFileResponse = new DropFileResponse(
            $file->getId()->__toString(),
            $file->getFileName(),
            $file->getPath(),
            $file->getDate()->format('Y-m-d H:i:s'),
        );
    }

    public function getResponse(): DropFileResponse
    {
        return $this->dropFileResponse;
    }
}
