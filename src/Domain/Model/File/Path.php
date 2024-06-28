<?php

namespace DriveManager\Domain\Model\File;

use DriveManager\Application\Service\DropFile\Exceptions\IncompletePathException;

class Path
{
    private string $path;

    public function __construct(string $path = '')
    {
        $this->validatePath($path);
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    private function validatePath(string $path): void
    {
        if ($this->isNextCloudPath($path) && !$this->isCompletePath($path)) {
            throw new IncompletePathException("Provider drive detect but seem incomplete. Please check path '$path'");
        }
    }

    private function isNextCloudPath(string $path): bool
    {
        return str_contains($path, 'owncloud') || str_contains($path, 'nextcloud');
    }

    private function isCompletePath(string $path): bool
    {
        return str_starts_with($path, 'https://') || str_starts_with($path, 'http://');
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
