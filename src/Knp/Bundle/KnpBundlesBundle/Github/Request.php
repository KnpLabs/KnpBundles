<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\HttpClient\HttpClient;

class Request extends HttpClient
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
     * @param  array    $options       Request options
     *
     * @return string   HTTP response
     */
    protected function doRequest($apiPath, array $parameters = array(), $httpMethod = 'GET', array $options = array())
    {
        for ($tries = 1; $tries <= $this->maxTries; $tries++) {
            return parent::doRequest($apiPath, $parameters, $httpMethod, $options);
        }
    }
}
