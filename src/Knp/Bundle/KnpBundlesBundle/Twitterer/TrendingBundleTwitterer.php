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
    private $idlePeriod;

    public function __construct(EntityManager $em, $tweetTemplate, TwitterApp $twitterService, $idlePeriod)
    {
        $this->em = $em;
        $this->tweetTemplate = $tweetTemplate;
        $this->twitterService = $twitterService;
        $this->idlePeriod = $idlePeriod;
    }

    public function tweet()
    {
        $message = $this->prepareMessage();
        echo $message . PHP_EOL; die;
        $this->twitterService->tweet($message);
    }

    private function prepareMessage()
    {
        if (!$trendingBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->findLatestTrend($this->idlePeriod)) {
            throw new TrendingBundleNotFoundException();
        }

        $bundleName = $trendingBundle->getName();

        $trendingBundle->setLastTweetedAt(new \DateTime());
        $this->em->persist($trendingBundle);
        $this->em->flush();

        $url = 'knpbundles.com/'.$trendingBundle->getUsername().'/'.$bundleName;

        $placeholders = array('{name}', '{url}');
        $values = array($bundleName, $url);

        return str_replace($placeholders, $values, $this->tweetTemplate);
    }
}
