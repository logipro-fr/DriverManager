<?php

namespace DriveManager;

require_once __DIR__ . '/../vendor/autoload.php';

use Exception;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDrive;

class DropFileGoogleDrive implements DropFileInterface
{
    private GoogleDrive $service;

    public function __construct(GoogleClient $client = null, GoogleDrive $service = null)
    {
        if (!$client) {
            $client = new GoogleClient();
            $client->addScope(GoogleDrive::DRIVE_FILE);
        }

        $this->service = $service ?: new GoogleDrive($client);
    }

    public function drop(string $filename, string $content): void
    {
        $file = new GoogleDrive\DriveFile();
        $file->setName($filename);
        $result = $this->service->files->create($file, [
            'data' => $content,
            'mimeType' => 'text/plain',
            'uploadType' => 'multipart'
        ]);
    }

    public function isFileExists(string $filename): bool
    {
        return true;
    }
}
