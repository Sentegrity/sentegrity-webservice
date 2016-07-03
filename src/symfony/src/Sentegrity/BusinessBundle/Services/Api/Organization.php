<?php
namespace Sentegrity\BusinessBundle\Services\Api;

use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Services\Service;

/**
 * This service won't use ORM to query database since it brings in an overhead.
 * That overhead was acceptable for services that are used by an admin since
 * admin is used in a much less percentage than web services.
 *
 * Here we are going to use a basic querying with a help of MySQLQuery query builder
 * that allows us a simple interface for writing basic queries. It uses PDO as it's
 * base to communicate with database.
 */

class Organization extends Service
{
    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
    }

    /**
     * Simply extracts domain name from an email. Throws an exception if parameter
     * is not formatted as an email
     * 
     * @param $email
     * @return string
     * @throws ValidatorException
     */
    public static function getDomainNameFromEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidatorException(
                null,
                'Email is not in a proper format',
                ErrorCodes::INVALID_METHOD_PARAMS
            );
        }

        $email = explode("@", $email);
        return $email[1];
    }

    /**
     * This method gets a domain from users activation id and tries to get
     * an organization with a given domain. It it exists it returns organization id.
     * Else it returns false.
     *
     * @param $userActivationId
     * @return int
     * @return bool
     */
    public function getOrganizationByDomainName($userActivationId)
    {
        /***/
        $domainName = self::getDomainNameFromEmail($userActivationId);
        $qr = $this->mysqlq->select(
            'organization',
            array('id'),
            array('domain_name' => array('value' => $domainName))
        );

        if (!$qr) {
            return 0;
        }

        return (int)$qr->id;
    }
}