<?php
namespace Sentegrity\BusinessBundle\BatchJobs;

use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Sentegrity\BusinessBundle\Handlers\Utility;

/**
 * Worker class is an abstract class that implements all necessary summary methods
 * that will be used to process run history data.
 */
abstract class Worker
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
     * This method is called by batch jobs when certain job needs to be executed.
     *
     * @param $time -> Time is a timestamp on when the job is started. We are using
     *                 an unique time for all organization to keep stuff more
     *                 structured and safe
     * @param $chunkSize -> Defines tha size of a chunk of data that is selected from
     *                      database in one try
     * @return bool
     * @throws ValidatorException
     */
    abstract public function execute($time, $chunkSize);

    /**
     * Summarize given data by calling specific summarize methods based on the
     * data type.
     *
     * @param array $data
     * @return array $summarizedData -> database ready data
     * @throws ValidatorException
     */
    abstract protected function summarize(array $data);

    /**
     * Creates new table if it does not exist.
     *
     * @param $tableNameTemplate
     * @param $organizationId
     * @param $time
     * @return string $table -> table name
     */
    protected function createTable($tableNameTemplate, $organizationId, $time)
    {

        $table = str_replace('{organization}', $organizationId, $tableNameTemplate);
        $table = str_replace('{time}', $time, $table);

        if ($this->mysqlq->raw(
              'CREATE TABLE IF NOT EXISTS `' . $table . '` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `user_activation_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT \'FK from user table\',
                  `device_salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `date` int(11) NOT NULL,
                  `phone_model` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `platform` smallint(6) NOT NULL,
                  `user_issues` longtext COLLATE utf8_unicode_ci NOT NULL,
                  `system_issues` longtext COLLATE utf8_unicode_ci NOT NULL,
                  `user_suggestions` longtext COLLATE utf8_unicode_ci NOT NULL,
                  `system_suggestions` longtext COLLATE utf8_unicode_ci NOT NULL,
                  `user_score` decimal(7,4) NOT NULL,
                  `device_score` decimal(7,4) NOT NULL,
                  `trust_score` decimal(7,4) NOT NULL,
                  `core_detection_result` int(11) NOT NULL,
                  PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
        )) {
            return $table;
        }
    }

    /**
     * Performs data insertion.
     *
     * @param array $basicData -> data that is copied from past table
     * @param array $summarizedData - summarized data
     * @param $table
     * @return bool
     */
    protected function insertData(array $basicData, array $summarizedData, $table)
    {
        if (empty($summarizedData)) {
            return true;
        }

        return $this->mysqlq->insert(
            $table,
            array(
                'user_activation_id',
                'device_salt',
                'date',
                'phone_model',
                'platform',
                'user_issues',
                'system_issues',
                'user_suggestions',
                'system_suggestions',
                'user_score',
                'device_score',
                'trust_score',
                'core_detection_result'
            ),
            array(
                'user_activation_id'    => array('value' => $basicData['user_activation_id']),
                'device_salt'           => array('value' => $basicData['device_salt']),
                'date'                  => array('value' => $basicData['date']),
                'phone_model'           => array('value' => $basicData['phone_model']),
                'platform'              => array('value' => $basicData['platform']),
                'user_issues'           => array('value' => $summarizedData['user_issues']),
                'system_issues'         => array('value' => $summarizedData['system_issues']),
                'user_suggestions'      => array('value' => $summarizedData['user_suggestions']),
                'system_suggestions'    => array('value' => $summarizedData['system_suggestions']),
                'user_score'            => array('value' => $summarizedData['user_score']),
                'device_score'          => array('value' => $summarizedData['device_score']),
                'trust_score'           => array('value' => $summarizedData['trust_score']),
                'core_detection_result' => array('value' => $summarizedData['core_detection_result'])
            )
        );
    }

    /**
     * Summary user issues from run history objects
     * @param array $userIssues
     * @return string -> JSON encoded, database ready string
     * @throws ValidatorException
     */
    protected function summaryUserIssues(array $userIssues)
    {
        return Utility::calculateNumberOfAppearances($userIssues);
    }

    /**
     * Summary system issues from run history objects
     * @param array $systemIssues
     * @return string -> JSON encoded, database ready string
     * @throws ValidatorException
     */
    protected function summarySystemIssues(array $systemIssues)
    {
        return Utility::calculateNumberOfAppearances($systemIssues);
    }

    /**
     * Summary user suggestions from run history objects
     * @param array $userSuggestions
     * @return string -> JSON encoded, database ready string
     * @throws ValidatorException
     */
    protected function summaryUserSuggestions(array $userSuggestions)
    {
        return Utility::calculateNumberOfAppearances($userSuggestions);
    }

    /**
     * Summary system suggestions from run history objects
     * @param array $systemSuggestions
     * @return string -> JSON encoded, database ready string
     * @throws ValidatorException
     */
    protected function summarySystemSuggestions(array $systemSuggestions)
    {
        return Utility::calculateNumberOfAppearances($systemSuggestions);
    }

    /**
     * Summary device score from run history objects
     * @param array $deviceScores
     * @return float -> average on given values
     * @throws ValidatorException
     */
    protected function summaryDeviceScore(array $deviceScores)
    {
        return Utility::calculateAverage($deviceScores);
    }

    /**
     * Summary trust score from run history objects
     * @param array $trustScores
     * @return float -> average on given values
     * @throws ValidatorException
     */
    protected function summaryTrustScore(array $trustScores)
    {
        return Utility::calculateAverage($trustScores);
    }

    /**
     * Summary user score from run history objects
     * @param array $userScores
     * @return float -> average on given values
     * @throws ValidatorException
     */
    protected function summaryUserScore(array $userScores)
    {
        return Utility::calculateAverage($userScores);
    }

    /**
     * Summary core detection result from run history objects
     * @param array $coreDetectionResults
     * @return integer -> most common value
     * @throws ValidatorException
     */
    protected function summaryCoreDetectionResult(array $coreDetectionResults)
    {
        return Utility::getMostCommonValue($coreDetectionResults);
    }

    /**
     * Checks if record exists, and if it is, add it.
     * @param $key
     * @param $record
     * @param $bucket
     */
    protected function add($key, &$record, &$bucket)
    {
        if (isset($record[$key])) {
            $bucket[] = $record[$key];
        }
    }

    /**
     * Checks if record exists, and if it is, do the merging.
     * @param $key
     * @param $record
     * @param $bucket
     */
    protected function merge($key, &$record, &$bucket)
    {
        if (isset($record[$key])) {
            $bucket = array_merge($bucket, $record[$key]);
        }
    }
}