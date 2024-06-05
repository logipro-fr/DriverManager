<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use DriveManager\DropFileForFileSystem;

/**
 * Defines application features from the specific context.
 */
class DropAFileContext implements Context
{
    //private string $rootPath;
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @When I deposit a file :fileToDeposit in the directory :directoryName
     */
    public function iDepositAFileInTheDirectory(string $fileToDeposit, string $directoryName): void
    {
        // $this->rootPath;
        // $fullPath = $this->rootPath . '/' . $directoryName;

        // if (!is_dir($fullPath)) {
        //     mkdir($fullPath, recursive: true);
        // }

        // $dropFile = new DropFileForFileSystem($fullPath);
        // //obtenir le contenu du fichier de manière appropriée
        // $fileContent = "Contenu du fichier";
        // $dropFile->drop($fileToDeposit, $fileContent);
        throw new PendingException();
    }

    /**
     * @Then the file :fileDeposit should be listed in the :directoryName directory
     */
    public function theFileShouldBeListedInTheDirectory(string $fileDeposit, string $directoryName): void
    {
        throw new PendingException();
    }
}
