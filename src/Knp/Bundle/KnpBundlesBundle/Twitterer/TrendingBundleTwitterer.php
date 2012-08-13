<?php

namespace Knp\Bundle\KnpBundlesBundle\Twitterer;

use Knp\Bundle\KnpBundlesBundle\Twitterer\Exception\TrendingBundleNotFoundException;
use Doctrine\ORM\EntityManager;
use Inori\TwitterAppBundle\Services\TwitterApp;

class TrendingBundleTwitterer
{
    private $em;
    private $tweetTemplate;
    private $twitterService;

    public function __construct(EntityManager $em, $tweetTemplate, TwitterApp $twitterService, $idlePeriod)
    {
        $this->em = $em;
        $this->tweetTemplate = $tweetTemplate;
        $this->twitterService = $twitterService;

        if (!$this->trendingBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findLatestTrend($idlePeriod)) {
            throw new TrendingBundleNotFoundException();
        }
    }

    public function tweet()
    {
        $message = $this->prepareMessage();
        $this->twitterService->tweet($message);

        if ($this->twitterService->getApi()->http_code == 200) {
            $this->checkBundleAsTweeted();

            return $this->trendingBundle;
        }

        return false;
    }

    private function prepareMessage()
    {
        $bundleName = $this->trendingBundle->getName();
        $url = 'knpbundles.com/'.$this->trendingBundle->getUsername().'/'.$bundleName;

        $placeholders = array('{name}', '{url}');
        $values = array($bundleName, $url);

        return str_replace($placeholders, $values, $this->tweetTemplate);
    }

    private function checkBundleAsTweeted()
    {
        $this->trendingBundle->setLastTweetedAt(new \DateTime());
        $this->em->persist($this->trendingBundle);
        $this->em->flush();
    }
}
