<?php

namespace DriveManager\Application\Service\DropFile;

class DropFileRequest
{
    public function __construct(
        public readonly string $fileId,
        public readonly string $fileToDeposit,
        public readonly string $filePathToDirectory,
        //public readonly string $fileDate,
        public readonly string $fileContent,
        public readonly string $apiName
    ) {
    }
}
