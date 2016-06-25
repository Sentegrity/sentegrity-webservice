<?php
namespace Sentegrity\BusinessBundle\Services\Support;


use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Ramsey\Uuid\Uuid as UuidGenerator;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class UUID
{
    const UUID_REGEX = "^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$";

    /** @var string $uuid */
    public static $uuid;

    public static function generateUuid($seed = 'default')
    {
        try {
            $uuid = UuidGenerator::uuid5(UuidGenerator::NAMESPACE_DNS, $seed . random_int(0, 2000000000));
            self::$uuid = $uuid->toString();
        } catch (UnsatisfiedDependencyException $e) {
            throw new ValidatorException($e);
        }
    }
}