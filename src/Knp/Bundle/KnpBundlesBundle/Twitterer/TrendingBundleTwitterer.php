<?php

namespace Knp\Bundle\KnpBundlesBundle\Twitterer;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Twitterer\Exception\TrendingBundleNotFoundException;
use Doctrine\ORM\EntityManager;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\TwitterResourceOwner;

class TrendingBundleTwitterer extends TwitterResourceOwner
{
    /**
     * @var EntityManager
     */
    private $em;
    /**
     * @var Bundle
     */
    private $trendingBundle;

    /**
     * @var array
     */
    private $twitterParams = array();

    /**
     * @var string
     */
    private $twitterApiUrl = 'https://api.twitter.com/1/statuses/update.json';

    /**
     * @throws TrendingBundleNotFoundException
     */
    public function configure()
    {
        if (!$this->trendingBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findLatestTrend($this->twitterParams['idle_period'])) {
            throw new TrendingBundleNotFoundException();
        }
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $template
     * @param string $idlePeriod
     * @param string $token
     * @param string $secret
     */
    public function setTwitterParams($template, $idlePeriod, $token, $secret)
    {
        $this->twitterParams = array(
            'tweet_template'     => $template,
            'idle_period'        => $idlePeriod,
            'oauth_token'        => $token,
            'oauth_token_secret' => $secret
        );
    }

    /**
     * @return boolean|Bundle
     */
    public function tweet()
    {
        $timestamp  = time();
        $parameters = array(
            'oauth_consumer_key'     => $this->getOption('client_id'),
            'oauth_timestamp'        => $timestamp,
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_token'            => $this->twitterParams['oauth_token'],
        );

        $parameters['oauth_signature'] = $this->signRequest('POST', $this->twitterApiUrl, $parameters, $this->twitterParams['oauth_token_secret']);

        $response = $this->httpRequest($this->twitterApiUrl, $this->prepareMessage(), $parameters, array(), 'POST');

        if ($response->isSuccessful()) {
            $this->trendingBundle->setLastTweetedAt(new \DateTime());
            $this->em->persist($this->trendingBundle);
            $this->em->flush();

            return $this->trendingBundle;
        }

        return false;
    }

    private function prepareMessage()
    {
        $bundleName = $this->trendingBundle->getName();
        $url = 'knpbundles.com/'.$this->trendingBundle->getUsername().'/'.$bundleName;

        return str_replace(array('{name}', '{url}'), array($bundleName, $url), $this->twitterParams['tweet_template']);
    }
}
