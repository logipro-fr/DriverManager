<?php

namespace DriveManager\Application\Service\DropFile;

class DropFileResponse
{
    public function __construct(
        public readonly string $createdFileId,
        public readonly string $createdFileToDeposit,
        public readonly string $createdPath,
        public readonly string $createdDate,
    ) {
    }
}
