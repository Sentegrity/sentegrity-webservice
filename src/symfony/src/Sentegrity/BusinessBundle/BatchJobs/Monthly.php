<?php
namespace Sentegrity\BusinessBundle\BatchJobs;

use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Monthly job gets data from weekly tables and makes a new level of summary.
 */
class Monthly extends Weekly
{
    private $tableNameTemplate = "monthly_{organization}_{time}";
    private $period = 2592000;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Execute method. This method is called by command "batch:monthly:execute". It executes
     * all necessary jobs to create all monthly tables for given month.
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
        $weeklyTables = $this->getListOfWeeklyTables($time);
        if(empty($weeklyTables)) {
            return true;
        }

        // now we should get all unique users from all daily tables per
        // each organization
        foreach ($weeklyTables as $organizationId => $weeklyTable) {
            $table = $this->createTable($this->tableNameTemplate, $organizationId, $originalTime);
            $deviceSalts = $this->getUniqueDeviceSaltsPerOrganization($weeklyTable, $organizationId);
            foreach ($deviceSalts as $deviceSalt) {
                $data = $this->getDataByDeviceSalt($deviceSalt->device_salt, $weeklyTable, $chunkSize);
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
            foreach ($weeklyTable as $table) {
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
     * Gets the list of weekly tables that needs to be summarized
     *
     * @param $time
     * @return array
     */
    private function getListOfWeeklyTables($time)
    {
        $tables = $this->mysqlq->slave()->raw('SHOW TABLES LIKE \'weekly_%\'', true, \PDO::FETCH_ASSOC);

        if (!$tables) {
            return [];
        }

        $weeklyTables = [];
        foreach ($tables as $table) {
            $weekly = explode("_", $table['Tables_in_sentegrity (weekly_%)']);
            // explode will result in
            // $weekly_ = array(0 => 'weekly_', 1 => {organization_id}, 2 => {time})
            if ($weekly[2] < $time) {
                $weeklyTables[$weekly[1]][] = $table['Tables_in_sentegrity (weekly_%)'];
            }
        }

        return $weeklyTables;
    }
}