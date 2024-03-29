<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Sentegrity\BusinessBundle\Handlers\Platform;
use Sentegrity\BusinessBundle\Services\Service;
use Sentegrity\BusinessBundle\Services\Support\Database\MySQLQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Policy extends Service
{
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Gets policy with higher revision number
     *
     * @param $policy
     * @param $revision
     * @param $platform
     * @return array
     */
    public function getNewPolicyRevision($policy, $revision, $platform)
    {
        /***/
        $qr = $this->mysqlq->select(
            'policy',
            array('data', 'name', 'app_version'),
            array(
                'id'            => array('value' => $policy),
                'revision_no'   => array('value' => $revision, 'logic' => MySQLQuery::_AND, 'operator' => '>'),
                'platform'      => array('value' => $platform, 'logic' => MySQLQuery::_AND)
            )
        );

        if (!$qr) {
            return null;
        }

        return [
            'data' => json_decode($qr->data),
            'name' => $qr->name,
            'app_version' => $qr->app_version
        ];
    }
    
    /**
     * Check if policy is default. It it is returns policyId
     * 
     * @param $policy
     * @param $platform
     * @param $organization
     * @return int
     */
    public function checkIfDefault($policy, $platform, $organization)
    {
        /***/
        $qr = $this->mysqlq->select(
            'policy',
            array('is_default', 'id'),
            array(
                'name'                  => array('value' => $policy),
                'platform'              => array('value' => $platform, 'logic' => MySQLQuery::_AND),
                'organization_owner_id' => array('value' => $organization, 'logic' => MySQLQuery::_AND)
            )
        );
        
        if (!$qr) {
            return false;
        }
        
        return ($qr->is_default) ? $qr->id : false;
    }

    /**
     * Gets policyId by combination of a group, organization and platform
     *
     * @param $groupId
     * @param $organizationId
     * @param $platform
     * @return int
     */
    public function getPolicyIdByGroupOrganizationPlatform($groupId, $organizationId, $platform)
    {
        /***/
        switch ($platform) {
            case Platform::IOS:
                $platform = 'policy_id_ios';
                break;
            case Platform::ANDROID:
                $platform = 'policy_id_android';
                break;
            default:
                // TODO: something
                break;
        }
        $qr = $this->mysqlq->select(
            'groups',
            array($platform),
            array(
                'group_id'          => array('value' => $groupId),
                'organization_id'   => array('value' => $organizationId, 'logic' => MySQLQuery::_AND)
            )
        );

        if (!$qr) {
            return false;
        }

        return $qr->$platform;
    }

    /**
     * Gets policy by ID
     *
     * @param $id
     * @return array
     */
    public function getPolicyById($id)
    {
        /***/
        $qr = $this->mysqlq->select(
            'policy',
            array('data', 'name', 'app_version'),
            array(
                'id' => array('value' => $id),
            )
        );

        if (!$qr) {
            return [];
        }

        return [
            'data' => json_decode($qr->data),
            'name' => $qr->name,
            'app_version' => $qr->app_version
        ];
    }

    /**
     * Gets policy by ID and app version
     *
     * @param $id
     * @return array
     */
    public function getPolicyByIdAndAppVersion($id, $version)
    {
        /***/
        $qr = $this->mysqlq->select(
            'policy',
            array('data', 'name', 'app_version'),
            array(
                'id' => array('value' => $id),
                'app_version' => array('value' => $version, 'logic' => MySQLQuery::_AND)
            )
        );

        if (!$qr) {
            return [];
        }

        return [
            'data' => json_decode($qr->data),
            'name' => $qr->name,
            'app_version' => $qr->app_version
        ];
    }
}