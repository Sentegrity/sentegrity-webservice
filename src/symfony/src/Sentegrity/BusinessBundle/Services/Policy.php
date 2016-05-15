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
     * @param $email
     *
     * @return \stdClass
     */
    public function checkPolicy($policyId, $policyRevision, $email)
    {
        /***/
        if ($policy = $this->selectProperPolicy($policyId, $email)) {
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
     * @param string $filenameStructure -> {policyId}
     *
     * @return \stdClass
     */
    private function getPolicyById($policyId, $filenameStructure = "{policyId}")
    {
        $policyFile = $filenameStructure;
        $policyFile = $this->policyLocationPath . str_replace("{policyId}", $policyId, $policyFile);

        if (file_exists($policyFile)) {
            return json_decode(file_get_contents($policyFile));
        }

        return null;
    }

    /**
     * Determines what policy should be used
     *
     * @param string $policyId
     * @param string $email
     *
     * @return \stdClass
     */
    private function selectProperPolicy($policyId, $email)
    {
        $defaultPolicy = $this->containerInterface->getParameter('default_policy');
        if ($policyId == $defaultPolicy) {
            $domain = explode("@", $email);
            $policyId = $domain[1];
        }

        $policy = $this->getPolicyById($policyId);
        if (!$policy) {
            $policy = $this->getPolicyById($defaultPolicy);
        }

        return $policy;
    }
}