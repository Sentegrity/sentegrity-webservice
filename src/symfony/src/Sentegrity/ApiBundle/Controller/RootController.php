<?php
namespace Sentegrity\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sentegrity\BusinessBundle\Handlers as Handler;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Sentegrity\BusinessBundle\Services\Support\ValidateRequest;

class RootController extends Controller
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Validates data from request against required set of data
     * @param Request $request
     * @param array $against
     * @return array $requestData
     */
    protected function validate($request, $against, $type = ValidateRequest::JSON)
    {
        $requestData = [];
        switch ($type) {
            case ValidateRequest::GET:
                $requestData = $request->query->all();
                break;
            case ValidateRequest::POST:
                $requestData = $request->request->all();
                break;
            case ValidateRequest::JSON:
                $requestData = json_decode($request->getContent(), true);
                break;
        }

        ValidateRequest::validateRequestBody(
            $requestData,
            $against
        );
        
        return $requestData;
    }

    /**
     * Makes OK response and returns it
     * @param $data -> data that needs to be returned
     * @return string -> json encoded
     */
    protected function response($data)
    {
        Handler\Response::responseOK($data);
        return Handler\Response::$response;
    }
    
    /**
     * Retrieves owner's uuid from header
     * @param HeaderBag $header
     * @return string $owner
     */
    protected function getOwnerFromHeader(HeaderBag $header)
    {
        $owner = "";
        if ($t = $header->get('owner')) {
            $owner = $t;
        }
        
        return $owner;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
