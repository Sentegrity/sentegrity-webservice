<?php
namespace Sentegrity\BusinessBundle\BatchJobs;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;

class Cleaner
{
    /** @var ContainerInterface $containerInterface */
    protected $containerInterface;

    /** @var MySQLQuery $mysqlq */
    protected $mysqlq;

    function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
        $this->mysqlq = $containerInterface->get('my_sql_query');
    }

    /**
     * Executes cleaner
     * @param $what
     */
    public function execute($what = 'row')
    {
        switch ($what) {
            case 'row':
                $this->cleanRows();
                break;
            case 'table':
                $this->cleanTables();
                break;
            case 'table_by_time':
                $this->cleanTablesByTime();
                break;
            default:
                break;
        }
    }

    /**
     * Delete all data where boolean flag is 1
     * @param $table
     * @param $row
     */
    private function cleanRows($table = '24_hour_run_history', $row = 'processed')
    {
        $this->mysqlq->delete(
            $table,
            array(
                $row => array('value' => 1)
            )
        );
    }

    /**
     * Delete all tables with given prefix
     */
    private function cleanTables($prefix = 'proc_')
    {
        $tables = $this->mysqlq->slave()->raw('SHOW TABLES LIKE \'' . $prefix . '%\'', true, \PDO::FETCH_ASSOC);
        if ($tables) {
            foreach ($tables as $table) {
                $this->mysqlq->raw('DROP TABLE ' . $table['Tables_in_sentegrity (' . $prefix . '%)']);
            }
        }
    }

    /**
     * Delete all tables older than month
     */
    private function cleanTablesByTime()
    {
        // tables that are older than month (+2 days to make some buffer)
        $limit = time() - 2764800;

        $tables = $this->mysqlq->slave()->raw('SHOW TABLES LIKE \'daily_%\'', true, \PDO::FETCH_ASSOC);
        if ($tables) {
            foreach ($tables as $table) {
                $daily = explode("_", $table['Tables_in_sentegrity (daily_%)']);
                // explode will result in
                // $daily = array(0 => 'daily', 1 => {organization_id}, 2 => {time})
                if ($daily[2] < $limit) {
                    $this->mysqlq->raw('DROP TABLE ' . $table['Tables_in_sentegrity (daily_%)']);
                }
            }
        }
    }
}