<?php

namespace DriveManager\Application\Service;

use DriveManager\Application\Service\DropFile\DropFileInterface;

abstract class ProviderAbstractFactory
{
    abstract public function create(string $apiName): DropFileInterface;
}
