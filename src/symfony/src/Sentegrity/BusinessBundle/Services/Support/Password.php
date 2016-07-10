<?php
namespace Sentegrity\BusinessBundle\Services\Support;


class Password
{
    /**
     * Seed and hash password from client. Used before user
     * creation.
     *
     * @param $password
     * @param $seed - optional
     * @return $password
     */
    public static function seedAndEncryptPassword($password, $seed = 'default')
    {
        $password = $password.$seed;
        return sha1($password);
    }
}