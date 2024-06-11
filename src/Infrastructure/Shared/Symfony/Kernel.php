<?php

namespace DriveManager\Infrastructure\Shared\Symfony;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Dotenv\Dotenv;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        // Chemin personnalisé pour les fichiers .env et .env.local
        $dotenvPath = __DIR__ . '/.env';

        // Initialiser Dotenv et charger les fichiers
        $dotenv = new Dotenv();
        $dotenv->load($dotenvPath);

        $envLocalPath = __DIR__ . '/.env.local';
        if (file_exists($envLocalPath)) {
            $dotenv->load($envLocalPath);
        }
    }
}
