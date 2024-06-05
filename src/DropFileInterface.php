<?php

namespace DriveManager;

interface DropFileInterface
{
    public function drop(string $filename, string $content): void;
    public function isFileExists(string $filename): bool;
}
