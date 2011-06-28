<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Github;

class Request extends \Github_Request
{
    /**
     * How many times retry to communicate with GitHub before giving up
     *
     * @var integer
     */
    protected $maxTries = 2;

    /**
     * Send a request to the server, receive a response
     *
     * @param  string   $apiPath       Request API path
     * @param  array    $parameters    Parameters
     * @param  string   $httpMethod    HTTP method to use
     *
     * @return string   HTTP response
     */
    public function doSend($apiPath, array $parameters = array(), $httpMethod = 'GET')
    {
        for ($tries = 1; $tries <= $this->maxTries; $tries++) {
            try {
                return parent::doSend($apiPath, $parameters, $httpMethod);
            } catch(\Github_HttpClient_Exception $e) {
                if(404 == $e->getCode()) {
                    throw $e;
                }
            }
        }

        throw $e;
    }
}
