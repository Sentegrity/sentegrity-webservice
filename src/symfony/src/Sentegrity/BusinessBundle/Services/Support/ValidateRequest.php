<?php
namespace Sentegrity\BusinessBundle\Services\Support;

class ValidateRequest
{
    const POST = 1;
    const GET = 2;
    const JSON = 3;

    /**
     * Validates Request JSON
     *
     * @param array $body -> decoded JSON body
     * @param array $requiredFields
     *
     * @return bool true
     * @throws \Exception
     */
    public static function validateRequestBody(
        array $body,
        array $requiredFields
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

    /**
     * Creates string out of an array
     *
     * @param array $unExisting
     *
     * @return string
     */
    private static function unExisting2String($unExisting)
    {
        return "Missing fields: (" . implode(", ", $unExisting) . ")";
    }
}