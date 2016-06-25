<?php
namespace Sentegrity\BusinessBundle\Services\Support;


use Sentegrity\BusinessBundle\Exceptions\ErrorCodes;
use Sentegrity\BusinessBundle\Exceptions\ValidatorException;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Sentegrity\BusinessBundle\Annotations\Validator as AnnotationConstants;

class Validator
{
    /** @var \Symfony\Component\HttpFoundation\Request */
    private $request;

    function __construct(\Symfony\Component\HttpFoundation\RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param $reqParams -> array of required parameters
     * @return \Symfony\Component\HttpFoundation\Response;
     * @throws \Exception
     */
    public function queryParameter($reqParams)
    {
        $invalid = new \stdClass();
        $invalid->missing = [];
        $invalid->invalidFormat = [];
        try {
            foreach ($reqParams as $param) {
                if ($param['format'] == AnnotationConstants::JSON) {
                    if (!$this->isJson($this->request->getContent())) {
                        $invalid->invalidFormat[] = "Content not JSON";
                    }
                } else if ($param['format'] == AnnotationConstants::FILE) {
                    $this->isFile($param, $invalid);

                } else {
                    $this->isBlank($param, $invalid);
                    $this->isValidFormat($param, $invalid);
                }
            }
            $invalid->missing = array_unique($invalid->missing);
            $invalid->invalidFormat = array_unique($invalid->invalidFormat);

            if (!$invalid->missing && !$invalid->invalidFormat) {
                $invalid = null;
            }

            if (!empty($invalid)) {
                $rsp = new \stdClass();
                $rsp->msg = "Invalid Fields";
                $rsp->fields = $invalid;
                return $rsp;
            } else {
                return false;
            }

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $rcvKey
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return bool
     */
    public static function validateApiKey($rcvKey, &$container)
    {
        if ($rcvKey == $container->getParameter('api_key')) {
            return false;
        }

        Handler\Response::responseUnauthorised('');
        return Handler\Response::$response;
    }

    private function isBlank($param, &$invalid)
    {
        if (!isset($param['allowBlank'])) {
            return false;
        } else {
            $data = $this->request->get($param['name']);
            if ($param['format'] == AnnotationConstants::NUMBER && (int)$data === 0 && !is_null($data)) {
                return false;
            }
            if (empty($data)) {
                $invalid->missing[] = $param['name'];
            }
        }
        return true;
    }

    private function isValidFormat($param, &$invalid)
    {
        $data = $this->request->get($param['name']);
        if ($data) {
            if (!call_user_func($param['format'], $data)) {
                $invalid->invalidFormat[] = $param['name'];
            } else {
                if ($param['format'] == AnnotationConstants::NUMBER && isset($param['format_type'])) {
                    $range = [];
                    $ranges = [
                        AnnotationConstants::NUMBER_RANGE,
                        AnnotationConstants::NUMBER_RANGE_EXCLUDE_BOTH,
                    ];
                    if (isset($param['range']) && in_array($param['format_type'], $ranges)) {
                        $range = explode(",", $param['range']);
                    }
                    if (!$this->validateNumber($data, $param['format_type'], $range)) {
                        $invalid->invalidFormat[] = $param['name'];
                    }
                } else if ($param['format'] == AnnotationConstants::STRING && isset($param['format_type'])) {
                    if (!$this->validateString($data, $param['format_type'])) {
                        $invalid->invalidFormat[] = $param['name'];
                    }
                }
            }
        }
    }

    private function isFile($param, &$invalid)
    {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $data */
        $data = $this->request->files->get($param['name']);
        if (isset($param['allowBlank'])) {
            if (!$data) {
                $invalid->missing[] = $param['name'];
            } else {
                if (!call_user_func($param['format'], $data->getPathname())) {
                    $invalid->invalidFormat[] = $param['name'];
                }
            }
        }
    }

    /**
     * @see http://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
     */
    private function isJson($string)
    {
        if (!$string) {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    private function validateNumber($number, $type, $range = [])
    {
        switch ($type) {
            case AnnotationConstants::NUMBER_POSITIVE:
                if ($number <= 0) {
                    return false;
                }
                break;
            case AnnotationConstants::NUMBER_NEGATIVE:
                if ($number >= 0) {
                    return false;
                }
                break;
            case AnnotationConstants::NUMBER_POSITIVE_ZERO:
                if ($number < 0) {
                    return false;
                }
                break;
            case AnnotationConstants::NUMBER_RANGE:
                if (!($number >= $range[0] && $number <= $range[1])) {
                    return false;
                }
                break;
            case AnnotationConstants::NUMBER_RANGE_EXCLUDE_BOTH:
                if (!($number > $range[0] && $number < $range[1])) {
                    return false;
                }
                break;
        }

        return true;
    }

    private function validateString($string, $type)
    {
        switch ($type) {
            case AnnotationConstants::STRING_UUID:
                if (!preg_match("/" . UUID::UUID_REGEX . "/", $string)) {
                    return false;
                }
                break;
            case AnnotationConstants::STRING_USERNAME:
                if (!preg_match(AnnotationConstants::STRING_USERNAME, $string)) {
                    throw new ValidatorException(
                        null,
                        "Username too short",
                        ErrorCodes::INVALID_METHOD_PARAMS
                    );
                }
                break;
            default:
                return true;
                break;
        }

        return true;
    }
}
