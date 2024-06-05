<?php

namespace DriveManager;

class ResponseGoogleDriveApi implements ResponseGoogleDriveApiInterface
{
    public function __construct(
        public readonly int $currentSpeed,
        public readonly bool $roadClosure,
        public readonly int $confidence,
        public readonly int $currentTravelTime,
        public readonly int $freeFlowSpeed
    ) {
    }
}
