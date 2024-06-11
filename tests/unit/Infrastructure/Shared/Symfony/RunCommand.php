<?php

namespace DriveManager\Infrastructure\Shared;

class CurrentWorkDirPath
{
    public static function getPath(): string
    {
        if (isset($_ENV["PWD"])) {
            return $_ENV["PWD"];
        }
        if (getenv('PWD')) {
            return getenv('PWD');
        }
        return getcwd() ? getcwd() : "";
    }
}
