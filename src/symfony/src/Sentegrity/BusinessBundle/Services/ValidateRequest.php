<?php
namespace Sentegrity\BusinessBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ValidateRequest
{
    /**
     * Validates Request JSON
     *
     * @param array $body -> decoded JSON body
     * @param array $requiredFields = ['email', 'deviceSalt', 'runHistoryObjects', 'policyID', 'policyRevision']
     *
     * @return bool true
     * @throws \Exception
     */
    public static function validateRequestBody(
        $body,
        $requiredFields = ['email', 'deviceSalt', 'runHistoryObjects', 'policyID', 'policyRevision']
    ) {
        /***/
        $unExisting = [];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $body)) {
                $unExisting[] = $field;
            }
        }

        if (!empty($unExisting)) {
            throw new \Exception(self::unExisting2String($unExisting), 200);
        }

        return true;
    }

    private static function unExisting2String($unExisting)
    {
        return "Missing fields: (" . implode(", ", $unExisting) . ")";
    }
}