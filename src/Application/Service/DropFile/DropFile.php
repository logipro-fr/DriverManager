<?php

namespace DriveManager\Application\Service\DropFile;

use DriveManager\Application\Service\ProviderAbstractFactory;
use DriveManager\Domain\Model\File\File;
use DriveManager\Domain\Model\File\FileName;
use DriveManager\Domain\Model\File\FileContent;
use DriveManager\Domain\Model\File\FileRepositoryInterface;
use DriveManager\Domain\Model\File\Path;
use DriveManager\Infrastructure\DriveProvider\DropFileForFileSystem;
use DriveManager\Infrastructure\DriveProvider\DropFileNextcloud;
use org\bovigo\vfs\vfsStream;

class DropFile
{
    private DropFileResponse $dropFileResponse;

    public function __construct(
        private ProviderAbstractFactory $dropFileProviderFactory,
        private FileRepositoryInterface $repository
    ) {
    }

    public function execute(DropFileRequest $request): void
    {
        $fullPath = $request->filePathToDirectory . $request->fileToDeposit;
        $path = new Path($fullPath);
        $fileName = new FileName($request->fileToDeposit);
        $fileContent = new FileContent($request->fileContent);
        $file = new File($fileName, $path, $fileContent);

        if ($request->apiName == "FileSystem") {
            /** @var DropFileForFileSystem $dropFileApi */
            $dropFileApi = $this->dropFileProviderFactory->create($request->apiName);
            $dropFileApi->createDirectory("$request->filePathToDirectory/$request->fileToDeposit");
        } else {
            /** @var DropFileNextcloud $dropFileApi */
            $dropFileApi = $this->dropFileProviderFactory->create($request->apiName);
        }

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
