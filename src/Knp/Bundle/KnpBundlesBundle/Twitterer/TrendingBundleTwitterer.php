<?php

namespace Knp\Bundle\KnpBundlesBundle\Twitterer;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Twitterer\Exception\TrendingBundleNotFoundException;
use Doctrine\ORM\EntityManager;
use Buzz\Message\Request;
use Buzz\Message\Response;
use Buzz\Client\ClientInterface;
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;

class TrendingBundleTwitterer
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var array
     */
    private $twitterParams = array();

    /**
     * @var string
     */
    private $twitterApiUrl = 'https://api.twitter.com/1/statuses/update.json';

    /**
     * @param EntityManager $em
     * @param ClientInterface
     */
    public function __construct(EntityManager $em, ClientInterface $buzz)
    {
        $this->em = $em;
        $this->httpClient = $buzz;
    }

    /**
     * @param string $template
     * @param string $idlePeriod
     * @param array  $twitterConfig
     */
    public function setTwitterParams($template, $idlePeriod, array $twitterConfig)
    {
        $this->twitterParams = array(
            'tweet_template'        => $template,
            'idle_period'           => $idlePeriod,
            'oauth_consumer_key'    => $twitterConfig['consumer_key'],
            'oauth_consumer_secret' => $twitterConfig['consumer_secret'],
            'oauth_token'           => $twitterConfig['oauth_token'],
            'oauth_token_secret'    => $twitterConfig['oauth_token_secret']
        );
    }

    /**
     * @return boolean|Bundle
     */
    public function tweet()
    {
        /* @var $trendingBundle Bundle*/
        if (!$trendingBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findLatestTrend($this->twitterParams['idle_period'])) {
            throw new TrendingBundleNotFoundException();
        }

        $timestamp  = time();
        $parameters = array(
            'oauth_consumer_key'     => $this->twitterParams['oauth_client_id'],
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => md5(microtime() . mt_rand()),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $this->twitterParams['oauth_token'],
        );

        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            'POST',
            $this->twitterApiUrl,
            $parameters,
            $this->twitterParams['oauth_client_secret'],
            $this->twitterParams['oauth_token_secret']
        );

        $response = $this->httpRequest($this->twitterApiUrl, $this->prepareMessage($trendingBundle), $parameters, array(), 'POST');

        if ($response->isSuccessful()) {
            $trendingBundle->setLastTweetedAt(new \DateTime());
            $this->em->persist($trendingBundle);
            $this->em->flush();

            return $trendingBundle;
        }

        return false;
    }

    /**
     * @param Bundle $trendingBundle
     *
     * @return string
     */
    private function prepareMessage(Bundle $trendingBundle)
    {
        $bundleName = $trendingBundle->getName();
        $url = 'knpbundles.com/'.$trendingBundle->getOwnerName().'/'.$bundleName;

        return str_replace(array('{name}', '{url}'), array($bundleName, $url), $this->twitterParams['tweet_template']);
    }

    /**
     * Code below is borrowed from HWIOAuthBundle
     *
     * @see HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GenericOAuth1ResourceOwner
     */
    private function httpRequest($url, $content = null, $parameters = array(), $headers = array(), $method)
    {
        $authorization = 'Authorization: OAuth';

        foreach ($parameters as $key => $value) {
            $value = rawurlencode($value);
            $authorization .= ", $key=\"$value\"";
        }

        $headers[] = $authorization;

        $request  = new Request($method, $url);
        $response = new Response();

        $request->setHeaders($headers);
        $request->setContent($content);

        $this->httpClient->send($request, $response);

        return $response;
    }
}
