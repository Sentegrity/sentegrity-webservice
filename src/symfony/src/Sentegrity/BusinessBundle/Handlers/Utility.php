<?php
namespace Sentegrity\BusinessBundle\Handlers;


class Utility
{
    /**
     * Calculates average from given data set and returns a result in
     * a given precision.
     *
     * @param array $bucket
     * @param $precision
     * @return float
     */
    public static function calculateAverage(array $bucket, $precision = 4)
    {
        if (empty($bucket)) {
            return 0;
        }

        return round(array_sum($bucket) / count($bucket), $precision);
    }

    /**
     * Gets an array and returns a JSON encoded string which contains how
     * many times certain value appears in that array.
     *
     * @param array $bucket
     * @return string -> JSON encoded
     */
    public static function calculateNumberOfAppearances(array $bucket)
    {
        if (empty($bucket)) {
            return json_encode([]);
        }

        return json_encode(
            array_count_values($bucket)
        );
    }

    /**
     * Returns the value that has most number of appearances in given array
     *
     * @param array $bucket
     * @return $result
     */
    public static function getMostCommonValue(array $bucket)
    {
        if (empty($bucket)) {
            return 0;
        }

        $appearances = array_count_values($bucket);
        $max = 0;
        $result = 0;
        foreach ($appearances as $key => $appearance) {
            if ($appearance > $max) {
                $max = $appearance;
                $result = $key;
            }
        }

        return $result;
    }
}