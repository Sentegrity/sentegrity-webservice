<?php
namespace Sentegrity\BusinessBundle\Services\Admin;

use Sentegrity\BusinessBundle\Handlers\Utility;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Sentegrity\BusinessBundle\Transformers\TopRisks;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Dashboard extends Service
{
    const TYPE_RISKS = 'R';
    const TYPE_USER = 'U';
    const TYPE_DEVICE = 'S';

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }
    
    /**
     * Get data for dashboard
     * 
     * @param $requestData
     * @return \Sentegrity\BusinessBundle\Transformers\GraphData $transformer
     */
    public function getGraphData(array $requestData)
    {
        $tables = $this->getTablesByOrganization($requestData['time_frame']);
        if (!$tables) {
            return [];
        }
        $transformer = new \Sentegrity\BusinessBundle\Transformers\GraphData();
        
        foreach ($tables as $time => $table) {
            $data = $this->mysqlq->slave()->select(
                $table,
                array(
                    'phone_model',
                    'platform',
                    'user_issues',
                    'system_issues',
                    'user_score',
                    'device_score',
                    'trust_score',
                    'user_activation_id',
                    'device_salt'
                ),
                [], [], [], '',
                MySQLQuery::MULTI_ROWS,
                \PDO::FETCH_ASSOC
            );

            foreach ($data as $item) {
                $transformer->setGraphData($item, $time - 86400);
            }
        }
        
        return $transformer;
    }

    /**
     * Get data for dashboard
     *
     * @param $requestData
     * @return \Sentegrity\BusinessBundle\Transformers\DashboardTopData $transformer
     */
    public function getTopData(array $requestData)
    {
        $tables = $this->getTablesByOrganization($requestData['time_frame']);
        if (!$tables) {
            return [];
        }
        $transformer = new \Sentegrity\BusinessBundle\Transformers\DashboardTopData();
        $userIssues = [];
        $systemIssues = [];
        $risks = [];

        foreach ($tables as $time => $table) {

            $data = $this->mysqlq->slave()->select(
                $table,
                array(
                    'phone_model',
                    'platform',
                    'user_issues',
                    'system_issues',
                    'user_score',
                    'device_score',
                    'trust_score',
                    'user_activation_id',
                    'device_salt'
                ),
                $this->setWhereForTopData($requestData),
                [], [], '',
                MySQLQuery::MULTI_ROWS,
                \PDO::FETCH_ASSOC
            );

            foreach ($data as $item) {
                Utility::sumCounts('user_issues', $item, $userIssues);
                Utility::sumCounts('system_issues', $item, $systemIssues);

                // risks we have to create manually 'coz we're gonna return array of objects
                $risks[] = new TopRisks($item);
            }
        }

        $transformer->setTopUserIssues(
            $this->findTopIssues($userIssues, self::TYPE_USER)
        );
        $transformer->setTopDeviceIssues(
            $this->findTopIssues($systemIssues, self::TYPE_DEVICE)
        );
        $transformer->setTopRisks(
            $this->findTopIssues($risks)
        );

        return $transformer;
    }

    /**
     * Get tables by organization for given time frame
     *
     * @param $timeFrame
     * @return array
     */
    private function getTablesByOrganization($timeFrame)
    {
        $period = $timeFrame * 86400;
        /** @var Organization $organization */
        $organization = $this->containerInterface->get('sentegrity_business.organization');
        $organizationId = $organization->getOrganizationIdByUuid($this->session->get('org_uuid'));
        $tables = $this->selectTable(time(), $period, $organizationId);

        if (!$tables) {
            return array();
        }

        return $tables;
    }
    
    /**
     * Get the tables for given time frame
     * 
     * @param $currentTime
     * @param $period
     * @param $organizationId
     * @return array $activeTables
     * @throws ValidatorException
     */
    private function selectTable($currentTime, $period, $organizationId)
    {
        /***/
        $tablePrefix = "daily_" . $organizationId . "_%";
        $tables = $this->mysqlq->slave()->raw(
            'SHOW TABLES LIKE \'' . $tablePrefix . '\'', 
            true, 
            \PDO::FETCH_ASSOC
        );

        if (!$tables) {
            throw new ValidatorException(
                null,
                "No data available for given time frame",
                ErrorCodes::NOT_FOUND
            );
        }
        
        $activeTables = [];
        foreach ($tables as $table) {
            $daily = explode("_", $table['Tables_in_sentegrity (' . $tablePrefix . ')']);
            // explode will result in
            // $daily = array(0 => 'daily', 1 => {organization_id}, 2 => {time})
            if ($daily[2] < $currentTime && $daily[2] > $currentTime - $period) {
                $activeTables[$daily[2]] = $table['Tables_in_sentegrity (' . $tablePrefix . ')'];
            }
        }

        return $activeTables;
    }

    /**
     * Used to get top X Issues by type
     * @param $data
     * @param $type
     * @param $limit
     * @return array
     */
    private function findTopIssues($data, $type = self::TYPE_RISKS, $limit = 5)
    {
        $list = [];

        switch ($type) {
            case self::TYPE_RISKS:
                usort($data, Utility::sortByObjectProperty('trustScore', 'ASC'));
                $known = [];
                $filtered = array_filter($data, function ($val) use (&$known) {
                    $unique = !in_array($val->userActivationId, $known);
                    $known[] = $val->userActivationId;
                    return $unique;
                });
                $list = array_slice($filtered, 0, $limit);
                break;
            case self::TYPE_USER:
                arsort($data);
                $list = array_slice($data, 0, $limit);
                break;
            case self::TYPE_DEVICE:
                arsort($data);
                $list = array_slice($data, 0, $limit);
                break;
            default:
                // this should never happen
                break;
        }

        return $list;
    }

    /**
     * Create where for top data - just to keep code easier to maintain
     * @param array $requestData
     * @return array $where
     */
    private function setWhereForTopData(array $requestData)
    {
        $where = array();
        if (isset($requestData['platform'])) {
            if ($requestData['platform']) {
                $platform = implode(",", $requestData['platform']);
                $where['platform'] = array(
                    'value' => $platform,
                    'in' => 1
                );
            }
        }
        if (isset($requestData['phone_model'])) {
            if ($requestData['phone_model']) {

                if (!empty($where)) {
                    $where['phone_model']['logic'] = MySQLQuery::_OR;
                    $where['phone_model']['group_close'] = 1;
                    $where['platform']['group_open'] = 1;
                }

                $phoneModel = "'" . implode("','", $requestData['phone_model']) . "'";
                $where['phone_model']['value'] = $phoneModel;
                $where['phone_model']['in'] = 1;
            }
        }

        if (isset($requestData['users'])) {
            if ($requestData['users']) {

                if (!empty($where)) {
                    $where['user_activation_id']['logic'] = MySQLQuery::_AND;
                }

                $users = "'" . implode("','", $requestData['users']) . "'";
                $where['user_activation_id']['value'] = $users;
                $where['user_activation_id']['in'] = 1;
            }
        }

        if (isset($requestData['devices'])) {
            if ($requestData['devices']) {

                if (!empty($where)) {
                    $where['device_salt']['logic'] = MySQLQuery::_OR;
                }

                $devices = "'" . implode("','", $requestData['devices']) . "'";
                $where['device_salt']['value'] = $devices;
                $where['device_salt']['in'] = 1;
            }
        }

        return $where;
    }
}