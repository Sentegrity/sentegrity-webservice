<?php
namespace Sentegrity\BusinessBundle\BatchJobs;

use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Weekly job gets data from daily tables and makes a new level of summary.
 */
class Weekly extends Worker
{
    private $tableNameTemplate = "weekly_{organization}_{time}";
    private $period = 0; //604800;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Execute method. This method is called by command "batch:weekly:execute". It executes
     * all necessary jobs to create all weekly tables for given week.
     *
     * @param $time
     * @param $chunkSize
     * @return bool
     * @throws ValidatorException
     */
    public function execute($time, $chunkSize)
    {
        $originalTime = $time;
        $time -= $this->period;
        $dailyTables = $this->getListOfDailyTables($time);
        if(empty($dailyTables)) {
            return true;
        }

        // now we should get all unique users from all daily tables per
        // each organization
        foreach ($dailyTables as $organizationId => $dailyTable) {
            $table = $this->createTable($this->tableNameTemplate, $organizationId, $originalTime);
            $deviceSalts = $this->getUniqueDeviceSaltsPerOrganization($dailyTable, $organizationId);
            foreach ($deviceSalts as $deviceSalt) {
                $data = $this->getDataByDeviceSalt($deviceSalt->device_salt, $dailyTable, $chunkSize);
                $data = $this->summarize($data);
                $basicData = [
                    'user_activation_id'    => $deviceSalt->user_activation_id,
                    'device_salt'           => $deviceSalt->device_salt,
                    'date'                  => $originalTime,
                    'phone_model'           => $deviceSalt->phone_model,
                    'platform'              => $deviceSalt->platform,
                ];
                $this->insertData($basicData, $data, $table);
            }

            // after table is processed rename it to format: "proc_{table_name}"
            // that will mark that table as processed
            foreach ($dailyTable as $table) {
                $this->mysqlq->raw('RENAME TABLE ' . $table . ' TO proc_' . $table);
            }
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
            'user_issues'              => json_encode($data['user_issues']),
            'system_issues'            => json_encode($data['system_issues']),
            'user_suggestions'         => json_encode($data['user_suggestions']),
            'system_suggestions'       => json_encode($data['system_suggestions']),
            'device_score'             => $this->summaryDeviceScore($data['device_scores']),
            'trust_score'              => $this->summaryTrustScore($data['trust_scores']),
            'user_score'               => $this->summaryUserScore($data['user_scores']),
            'core_detection_result'    => json_encode($data['core_detection_results'])
        ];
    }
    
    /**
     * Gets unique device salts from all daily tables for one organization
     * 
     * @param array $tables
     * @param $organizationId
     * @return array
     */
    protected function getUniqueDeviceSaltsPerOrganization(array $tables, $organizationId)
    {
        $query = "SELECT DISTINCT(device_salt) as device_salt, user_activation_id, phone_model, platform FROM (";

        $counter = 0;
        foreach ($tables as $table) {
            $query .= "SELECT DISTINCT(device_salt) as device_salt, user_activation_id, phone_model, platform FROM " . $table . " " .$table;
            if ($counter < count($tables) -1) {
                $query .= " UNION ";
            }
            $counter++;
        }
        $query .= ") temp_" . $organizationId;

        return $this->mysqlq->slave()->raw($query, true);
    }

    /**
     * Gets data from 24 hour run history by given device salt
     *
     * @param $deviceSalt
     * @param $chunkSize
     * @return $allData
     */
    protected function getDataByDeviceSalt($deviceSalt, $tables, $chunkSize)
    {
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

        // Step 1. Get data from database daily tables by device salt
        foreach ($tables as $table) {

            $rawData = $this->mysqlq->slave()->select(
                $table,
                array(
                    'user_issues',
                    'system_issues',
                    'user_suggestions',
                    'system_suggestions',
                    'user_score',
                    'device_score',
                    'trust_score',
                    'core_detection_result'
                ),
                array('device_salt' => array('value' => $deviceSalt)),
                [],
                array("offset" => 0, "limit" => $chunkSize),
                '',
                MySQLQuery::MULTI_ROWS,
                \PDO::FETCH_ASSOC
            );

            // first set of data is already retrieved so counter starts with 1
            $counter = 1;

            while ($rawData) {

                $data = $this->createDataSetsPerDeviceSalt($rawData);
                $this->sumCounts('user_issues', $data, $allData['user_issues']);
                $this->sumCounts('system_issues', $data, $allData['system_issues']);
                $this->sumCounts('user_suggestions', $data, $allData['user_suggestions']);
                $this->sumCounts('system_suggestions', $data, $allData['system_suggestions']);
                $this->merge('device_scores', $data, $allData['device_scores']);
                $this->merge('trust_scores', $data, $allData['trust_scores']);
                $this->merge('user_scores', $data, $allData['user_scores']);
                $this->sumCounts('core_detection_results', $data, $allData['core_detection_results']);

                $rawData = $this->mysqlq->slave()->select(
                    $table,
                    array(
                        'user_issues',
                        'system_issues',
                        'user_suggestions',
                        'system_suggestions',
                        'user_score',
                        'device_score',
                        'trust_score',
                        'core_detection_result'
                    ),
                    array('device_salt' => array('value' => $deviceSalt)),
                    [],
                    array("offset" => $counter * $chunkSize, "limit" => $chunkSize),
                    '',
                    MySQLQuery::MULTI_ROWS,
                    \PDO::FETCH_ASSOC
                );

                $counter++;
            }
        }

        return $allData;
    }

    /**
     * Gets the list of daily tables that needs to be summarized
     *
     * @param $time
     * @return array
     */
    private function getListOfDailyTables($time)
    {
        $tables = $this->mysqlq->slave()->raw('SHOW TABLES LIKE \'daily_%\'', true, \PDO::FETCH_ASSOC);

        if (!$tables) {
            return [];
        }

        $dailyTables = [];
        foreach ($tables as $table) {
            $daily = explode("_", $table['Tables_in_sentegrity (daily_%)']);
            // explode will result in
            // $daily = array(0 => 'daily', 1 => {organization_id}, 2 => {time})
            if ($daily[2] < $time) {
                $dailyTables[$daily[1]][] = $table['Tables_in_sentegrity (daily_%)'];
            }
        }

        return $dailyTables;
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


        foreach ($rawData as $record) {
                $this->sumCounts('user_issues', $record, $dataSets['user_issues']);
                $this->sumCounts('system_issues', $record, $dataSets['system_issues']);
                $this->sumCounts('user_suggestions', $record, $dataSets['user_suggestions']);
                $this->sumCounts('user_suggestions', $record, $dataSets['user_suggestions']);
                $this->add('device_score', $record, $dataSets['device_scores']);
                $this->add('trust_score', $record, $dataSets['trust_scores']);
                $this->add('user_score', $record, $dataSets['user_scores']);
                $this->sumCounts('core_detection_result', $record, $dataSets['core_detection_results']);
        }

        return $dataSets;
    }

    /**
     * Checks if record exists, and if it is, do the merging.
     * @param $key
     * @param $record
     * @param $bucket
     */
    private function sumCounts($key, &$record, &$bucket)
    {
        if (isset($record[$key])) {
            if (is_string($record[$key])) {
                $record[$key] = json_decode($record[$key], true);
            }

            array_walk_recursive($record[$key], function($item, $key) use (&$bucket){
                $bucket[$key] = isset($bucket[$key]) ?  $item + $bucket[$key] : $item;
            });
        }
    }
}