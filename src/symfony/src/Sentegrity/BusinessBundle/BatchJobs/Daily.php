<?php
namespace Sentegrity\BusinessBundle\BatchJobs;

use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Batch job that creates daily table on given data. This one is more specific
 * since it gets data from 24 hour run history which is raw data.
 */
class Daily extends Worker
{
    private $tableNameTemplate = "daily_{organization}_{time}";
    
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }
    
    /**
     * Execute method. This method is called by command "batch:daily:execute". It executes
     * all necessary jobs to create all daily tables for given day.
     * 
     * @param $time 
     * @param $chunkSize
     * @return bool
     * @throws ValidatorException
     */
    public function execute($time, $chunkSize)
    {
        // we are looking for ata older than 24 hours so
        $originalTime = $time;
        $time = $time - 86400;

        // Step 1. get all unique device salts older than 24 hour
        $records = $this->mysqlq->slave()->select(
            '24_hour_run_history',
            array('DISTINCT(device_salt) as device_salt', 'organization_id', 'phone_model', 'user_activation_id', 'platform'),
            array('upload_timestamp' => array(
                'value' => $time, 'operator' => '<'
            )),
            [], [], '',
            MySQLQuery::MULTI_ROWS
        );

        foreach ($records as $record) {
            $table = $this->createTable($this->tableNameTemplate, $record->organization_id, $originalTime);
            $data = $this->getDataByDeviceSalt($record->device_salt, $time, $chunkSize);
            $data = $this->summarize($data);
            $basicData = [
                'user_activation_id'    => $record->user_activation_id,
                'device_salt'           => $record->device_salt,
                'date'                  => $originalTime,
                'phone_model'           => $record->phone_model,
                'platform'              => $record->platform,
            ];
            $this->insertData($basicData, $data, $table);
        }

        return true;
    }

    /**
     * Summarize data
     * 
     * @param array $data
     * @return array $summarizedData
     * @throws ValidatorException
     */
    protected function summarize(array $data)
    {
        return [
            'user_issues'              => $this->summaryUserIssues($data['user_issues']),
            'system_issues'            => $this->summarySystemIssues($data['system_issues']),
            'user_suggestions'         => $this->summaryUserSuggestions($data['user_suggestions']),
            'system_suggestions'       => $this->summarySystemSuggestions($data['system_suggestions']),
            'device_score'             => $this->summaryDeviceScore($data['device_scores']),
            'trust_score'              => $this->summaryTrustScore($data['trust_scores']),
            'user_score'               => $this->summaryUserScore($data['user_scores']),
            'core_detection_result'    => $this->summaryCoreDetectionResult($data['core_detection_results'])
        ];
    }
    
    /**
     * This method creates data sets for each unique device salt that is retrieved
     * from database. It also prepares data so that it can be sent to summary methods.
     * 
     * @param array $rawData
     * @return array $data
     * @throws ValidatorException
     */
    private function createDataSetsPerDeviceSalt(array $rawData)
    {
        $dataSets = [
            'user_issues'               => [],
            'system_issues'             => [],
            'user_suggestions'          => [],
            'system_suggestions'        => [],
            'device_scores'             => [],
            'trust_scores'              => [],
            'user_scores'               => [],
            'core_detection_results'    => []
        ];


        foreach ($rawData as $records) {
            $json = json_decode($records->json, true);
            foreach ($json as $record) {
                $this->merge('userIssues', $record, $dataSets['user_issues']);
                $this->merge('systemIssues', $record, $dataSets['system_issues']);
                $this->merge('userSuggestions', $record, $dataSets['user_suggestions']);
                $this->merge('systemSuggestions', $record, $dataSets['system_suggestions']);
                $this->add('deviceScore', $record, $dataSets['device_scores']);
                $this->add('trustScore', $record, $dataSets['trust_scores']);
                $this->add('userScore', $record, $dataSets['user_scores']);
                $this->add('coreDetectionResult', $record, $dataSets['core_detection_results']);
            }
        }

        return $dataSets;
    }

    /**
     * Gets data from 24 hour run history by given device salt
     *
     * @param $deviceSalt
     * @param $time
     * @param $chunkSize
     * @return $allData
     */
    private function getDataByDeviceSalt($deviceSalt, $time, $chunkSize)
    {
        // Step 1. Get data from database by given time
        $rawData = $this->mysqlq->slave()->select(
            '24_hour_run_history',
            array('id', 'json'),
            array(
                'upload_timestamp' => array('value' => $time, 'operator' => '<'),
                'device_salt' => array('value' => $deviceSalt, 'logic' => MySQLQuery::_AND)
            ),
            [],
            array("offset" => 0, "limit" => $chunkSize),
            '',
            MySQLQuery::MULTI_ROWS
        );

        // first set of data is already retrieved so counter starts with 1
        $counter = 1;
        $allData = [
            'user_issues'               => [],
            'system_issues'             => [],
            'user_suggestions'          => [],
            'system_suggestions'        => [],
            'device_scores'             => [],
            'trust_scores'              => [],
            'user_scores'               => [],
            'core_detection_results'    => []
        ];
        $flag = [];
        while ($rawData) {

            $data = $this->createDataSetsPerDeviceSalt($rawData);
            $this->merge('user_issues', $data, $allData['user_issues']);
            $this->merge('system_issues', $data, $allData['system_issues']);
            $this->merge('user_suggestions', $data, $allData['user_suggestions']);
            $this->merge('system_suggestions', $data, $allData['system_suggestions']);
            $this->merge('device_scores', $data, $allData['device_scores']);
            $this->merge('trust_scores', $data, $allData['trust_scores']);
            $this->merge('user_scores', $data, $allData['user_scores']);
            $this->merge('core_detection_results', $data, $allData['core_detection_results']);

            foreach ($rawData as $item) {
                $flag[] = $item->id;
            }

            $rawData = $this->mysqlq->slave()->select(
                '24_hour_run_history',
                array('id', 'json'),
                array(
                    'upload_timestamp' => array('value' => $time, 'operator' => '<'),
                    'device_salt' => array('value' => $deviceSalt, 'logic' => MySQLQuery::_AND)
                ),
                [],
                array("offset" => $counter * $chunkSize, "limit" => $chunkSize),
                '',
                MySQLQuery::MULTI_ROWS
            );

            $counter++;
        }

        // TODO: update flags
        return $allData;
    }
}