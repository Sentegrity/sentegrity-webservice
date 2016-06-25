<?php
namespace Sentegrity\BusinessBundle\Handlers;


class JString
{
    public static function array2CSString($array, $quotes = false)
    {
        $string = "";
        $counter = 0;
        foreach ($array as $item) {
            if ($quotes) {
                $string .= "'" . $item . "'";
            } else {
                $string .= $item;
            }
            if ($counter < count($array) - 1) {
                $string .= ", ";
            }
            $counter++;
        }

        return $string;
    }
}