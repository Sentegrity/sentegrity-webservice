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
}