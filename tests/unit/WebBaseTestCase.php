<?php

namespace DriveManager\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Dotenv\Dotenv;

class WebBaseTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Chargez le Dotenv si ce n'est pas déjà fait dans votre application
        $dotenv = new Dotenv();
        $dotenv->usePutenv(); // Assurez-vous que putenv est utilisé
        $dotenv->load(__DIR__ . '/../.env.test');

        // Définir dynamiquement l'API_KEY à partir des variables d'environnement
        $apiKey = getenv('API_KEY_NEXTCLOUD');
        $apiKey = getenv('MAIL_ADDRESS');
    }
}
