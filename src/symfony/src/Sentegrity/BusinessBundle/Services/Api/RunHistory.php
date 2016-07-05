<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Service;

class RunHistory extends Service
{
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Save Run History
     *
     * @param array $runHistoryData
     * @return bool
     */
    public function save(array $runHistoryData)
    {
        /**
         * $runHistoryData template:
         * array(
         *      "user_activation_id" => ...,
         *      "organization_id" => ...,
         *      "device_salt" => ...,
         *      "phone_model" => ...,
         *      "platform" => ...,
         *      "objects" => array,
         * )
         */

        // create JSON to store
        $historyJSON = json_encode($runHistoryData['objects']);

        return $this->mysqlq->insert(
            '24_hour_run_history',
            array(
                'device_salt',
                'upload_timestamp',
                'phone_model',
                'platform',
                'json',
                'user_activation_id',
                'organization_id'
            ),
            array(
                'device_salt'           => array('value' => $runHistoryData['device_salt']),
                'upload_timestamp'      => array('value' => time()),
                'phone_model'           => array('value' => $runHistoryData['phone_model']),
                'platform'              => array('value' => $runHistoryData['platform']),
                'json'                  => array('value' => $historyJSON),
                'user_activation_id'    => array('value' => $runHistoryData['user_activation_id']),
                'organization_id'       => array('value' => $runHistoryData['organization_id'])
            )
        );
    }
}