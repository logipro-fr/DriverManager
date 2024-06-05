<?php

namespace DriveManager;

class RequestGoogleDriveApi implements RequestGoogleDriveApiInterface
{
    public function __construct(public readonly string $latitude, public readonly string $longitude)
    {
    }
}
