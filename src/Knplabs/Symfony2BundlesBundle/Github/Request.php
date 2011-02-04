<?php

namespace Knplabs\Symfony2BundlesBundle\Github;

require_once __DIR__.'/../../../../vendor/php-github-api/lib/request/phpGitHubApiRequest.php';

class Request extends \phpGitHubApiRequest
{
    /**
     * How many times retry to communicate with GitHub before giving up
     *
     * @var int
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
        for($tries = 1; $tries <= $this->maxTries; $tries++) {
            try {
                return parent::doSend($apiPath, $parameters, $httpMethod);
            }
            catch(\phpGithubApiRequestException $e) {
                if(404 == $e->getCode()) {
                    throw $e;
                }
            }
        }

        throw $e;
    }
}
