<?php
namespace Sentegrity\BusinessBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class RunHistory extends Service
{
    private $filesStoragePath;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->filesStoragePath = $containerInterface->getParameter('data_location');
    }

    /**
     * Save Run History Object in a file email_deviceSalt.json
     * If file exists append new data, otherwise create a new one
     *
     * @param string $email                -> field uniquely identifies the user
     * @param string $deviceSalt           -> uniquely identifies the device
     * @param array $runHistoryObjects     -> data to store
     *
     * @return bool true
     */
    public function saveRunHistoryObjects(
        $email,
        $deviceSalt,
        array $runHistoryObjects
    ) {
        /***/

        // validate parameters
        if (!$email || !$deviceSalt || !$runHistoryObjects) {
            throw new \Exception("Data parameters invalid", 200);
            // we can do more advance validation later
        }

        $filePath = $this->filesStoragePath . $this->createFilename($email, $deviceSalt);

        if (file_exists($filePath)) {
            if (!$this->appendToExisting($filePath, $runHistoryObjects)) {
                throw new \Exception("Adding to existing file failed", 100);
            }
        } else {
            if (!$this->addNewData($filePath, $runHistoryObjects)) {
                throw new \Exception("Adding to new file failed", 101);
            }
        }

        return true;
    }


    /**
     * Creates filename from email and device salt
     *
     * @param string $email
     * @param string $deviceSalt
     * @param string $filenameStructure -> {email}_{deviceSalt}.json
     *
     * @return string $filename
     */
    private function createFilename(
        $email,
        $deviceSalt,
        $filenameStructure = "{email}_{deviceSalt}.json"
    )
    {
        $filename = $filenameStructure;
        $filename = str_replace("{email}", $email, $filename);
        return str_replace("{deviceSalt}", $deviceSalt, $filename);
    }

    /**
     * Append new data to an existing one
     *
     * @param string $filePath
     * @param array $runHistoryObjects
     *
     * @return bool true/false
     */
    private function appendToExisting($filePath, array $runHistoryObjects)
    {
        // first load in existing objects and turn them into
        // an array
        $existingRunHistoryObjects = json_decode(file_get_contents($filePath),true);

        // then merge new to existing
        $runHistoryObjects = array_merge($existingRunHistoryObjects, $runHistoryObjects);

        // and at the end encode an store data to file
        // since we append data in code returning data is actually creating
        // a new file with merged values so we can use method for adding new data
        if ($this->addNewData($filePath, $runHistoryObjects)) {
            return true;
        }

        return false;
    }

    /**
     * creates a new data bucket
     *
     * @param string $filePath
     * @param array $runHistoryObjects
     *
     * @return bool true/false
     */
    private function addNewData($filePath, array $runHistoryObjects)
    {
        // try to put data into file on the given path
        if (file_put_contents(
            $filePath,
            json_encode($runHistoryObjects),
            LOCK_EX
        )) {
            return true;
        }

        return false;
    }
}