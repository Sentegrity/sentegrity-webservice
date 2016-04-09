<?php
namespace Sentegrity\BusinessBundle\Handlers;

use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Sentegrity\BusinessBundle\Transformers\Response as OutputResponse;

class Response
{
    /**
     * @var HttpResponse
     */
    public static $response;

    public static $metadata = null;

    public static function responseOK($data)
    {
        $response = new OutputResponse(self::$metadata, null, $data);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(200);
    }

    public static function responseOKNoJson($data)
    {
        self::$response = new HttpResponse($data);
        self::$response->setStatusCode(200);
    }

    public static function responseNotFound($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(404);
    }

    public static function responseBadRequest($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(400);
    }

    public static function responseForbidden($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(403);
    }

    public static function responseServiceUnavailable($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(503);
    }

    public static function responseInternalServerError($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(500);
    }

    public static function responseUnauthorised($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(401);
    }

    public static function responseGone($data)
    {
        $response = new OutputResponse(self::$metadata, $data, null);
        self::$response = new HttpResponse(json_encode($response));
        self::$response->setStatusCode(410);
    }
} 