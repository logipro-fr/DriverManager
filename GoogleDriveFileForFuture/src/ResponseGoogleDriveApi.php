<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\ResponseGoogleDriveApiInterface;

class ResponseGoogleDriveApi implements ResponseGoogleDriveApiInterface
{
    public function __construct(
        public readonly string $kind = "drive#file",
        public readonly string $id = "",
        public readonly string $name = "",
        public readonly string $mimeType = "text/plain", //texte brute
    ) {
    }
}
