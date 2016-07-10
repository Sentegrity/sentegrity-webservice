<?php
namespace Sentegrity\BusinessBundle\Annotations;

use Symfony\Component\Validator\Constraints\Required;

/**
 * @Annotation
 */

class Permission
{
    const SUPERADMIN = 0;
    const ADMIN = 1;
    const WRITE = 2;
    const READ = 3;

    /** @Required */
    public $permission;
}