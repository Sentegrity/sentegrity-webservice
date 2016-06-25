<?php
namespace Sentegrity\BusinessBundle\Services\Support\Database;


class PDOTransformers
{
    /**
     * Creates comma separated string of values by given key from an array of
     * objects
     */
    public static function generateIDs($objects, $key)
    {
        if (!is_array($objects)) {
            $objects = array($objects);
        }

        $IDs = "";
        $maxNo = count($objects);
        $counter = 1;

        foreach ($objects as $object) {
            $IDs .= "'" . $object->$key . "'";

            if ($counter < $maxNo) {
                if(!empty($object->$key))
                    $IDs .= ",";
            }

            $counter++;
        }

        if(substr($IDs, -1, 1) == ',') {
            $IDs = substr($IDs, 0, -1);
        }

        return $IDs;
    }

    /**
     * Sets objects id parameter as key and object as a value in an
     * array
     */
    public static function idsKeys($objects, $key = 'id')
    {
        $returnObjects = array();

        if (!is_array($objects)) {
            $objects = array($objects);
        }

        foreach ($objects as $object) {
            $returnObjects[$object->$key] = $object;
        }

        return $returnObjects;
    }
}