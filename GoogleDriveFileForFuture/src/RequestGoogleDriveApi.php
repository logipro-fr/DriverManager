<?php

namespace DriveManager\Infrastructure\DriveProvider;

use DriveManager\Application\Service\DropFile\RequestGoogleDriveApiInterface;

class RequestGoogleDriveApi implements RequestGoogleDriveApiInterface
{
    public function __construct(public readonly string $fileName, public readonly string $content)
    {
    }
}
