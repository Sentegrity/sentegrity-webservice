<?php
namespace Sentegrity\BusinessBundle\Annotations;

use Symfony\Component\Validator\Constraints\Required;

/**
 * @Annotation
 */
class Validator
{
    const STRING    = 'is_string';
    const NUMBER    = 'is_numeric';
    const JSON      = 'isJson';
    const FILE      = 'is_file';

    const NO    = true;

    const NUMBER_POSITIVE = '+';
    const NUMBER_NEGATIVE = '-';
    const NUMBER_POSITIVE_ZERO = '+0';
    const NUMBER_RANGE = '()';
    const NUMBER_RANGE_EXCLUDE_BOTH = "<>";

    const STRING_UUID = 'u';
    const STRING_USERNAME = '/^[a-z0-9][a-z0-9._]{4,45}$/';

    /** @Required */
    public $key = [];
} 