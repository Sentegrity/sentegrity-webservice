<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Services\Support\UUID;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This service won't use ORM to query database since it brings in an overhead.
 * That overhead was acceptable for services that are used by an admin since 
 * admin is used in a much less percentage than web services. 
 *
 * Here we are going to use a basic querying with a help of MySQLQuery query builder
 * that allows us a simple interface for writing basic queries. It uses PDO as it's
 * base to communicate with database.
 */

class User extends Service
{
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Creates user from given parameters
     *
     * @param array $userData
     * @return bool
     */
    public function create(array $userData)
    {
        /**
         * $userData template:
         * array(
         *      "device_activation_id" => ...,
         *      "organization_id" => ...,
         *      "group_id" => ...,
         * )
         */

        $deviceUserId = UUID::generateUuid($userData['device_activation_id']);
        return $this->mysqlq->insert(
            'user',
            array('device_user_id', 'device_activation_id', 'organization_id', 'group_id'),
            array(
                'device_user_id'        => array('value' => $deviceUserId),
                'device_activation_id'  => array('value' => $userData['device_activation_id']),
                'organization_id'       => array('value' => $userData['organization_id']),
                'group_id'              => array('value' => $userData['group_id'])
            )
        );
    }

    /**
     * This method is used to get group and organization id for given user. It is
     * also used to determine if user exists. If no record is returned by the query
     * than there is no user with that activation ID.
     *
     * @param $userActivationId
     * @return array
     * @return bool
     */
    public function getGroupAndOrganization($userActivationId)
    {
        /***/
        $rsp = $this->mysqlq->select(
            'user',
            array('organization_id', 'group_id'),
            array('device_activation_id' => array('value' => $userActivationId))
        );
        
        if (!$rsp) {
            return false;
        }
        
        return [
            'group_id' => $rsp->group_id,
            'organization_id' => $rsp->organization_id
        ];
    }
}