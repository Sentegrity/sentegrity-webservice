<?php
namespace Sentegrity\BusinessBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Policy  extends Service
{
    private $policyLocationPath;

    function __construct(ContainerInterface $containerInterface)
    {
        parent::__construct($containerInterface);
        $this->policyLocationPath = $containerInterface->getParameter('policy_location');
    }

    /**
     * Check if policy is valid, if not returns a new one. If not returns a null object
     *
     * @param $policyId
     * @param $policyRevision
     *
     * @return \stdClass
     */
    public function checkPolicy($policyId, $policyRevision)
    {
        /***/
        if ($policy = $this->getPolicyById($policyId)) {
            // check revision field
            if ($policy->revision != $policyRevision) {
                return $policy;
            }
            
            return null;
        }

        return null;
    }

    /**
     * Gets policy file by it's ID. If it does not exist, null is returned
     *
     * @param string $policyId
     * @param string $filenameStructure -> {email}_{deviceSalt}.json
     *
     * @return \stdClass
     */
    private function getPolicyById($policyId, $filenameStructure = "{policyId}.policy")
    {
        $policyFile = $filenameStructure;
        $policyFile = $this->policyLocationPath . str_replace("{policyId}", $policyId, $policyFile);

        if (file_exists($policyFile)) {
            return json_decode(file_get_contents($policyFile));
        }

        return null;
    }
}