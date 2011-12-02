<?php

namespace Knp\Bundle\KnpBundlesBundle\Twitterer;

use Knp\Bundle\KnpBundlesBundle\Twitterer\Exception\TrendingBundleNotFound;

class TrendingBundleTwitterer
{
	private $em;
	private $tweetTemplate;
	private $twitterService;

	public function __construct($em, $tweetTemplate, $twitterService)
	{
		$this->em = $em;
		$this->tweetTemplate = $tweetTemplate;
		$this->twitterService = $twitterService;
	}

	public function tweet()
	{
		$message = $this->prepareMessage();
		$this->twitterService->tweet($message);
	}

	private function prepareMessage()
	{
		if (!$trendingBundle = $this->em->getRepository('KnpBundlesBundle:Bundle')->getMostTrendingBundle()) {
			throw new TrendingBundleNotFound();
		}

		$bundleName = $trendingBundle->getName();
		$url = 'bundles.knplabs.org/' . $trendingBundle->getUsername() . '/' . $bundleName;

		$message = str_replace('{name}', $bundleName, $this->tweetTemplate);
		$message = str_replace('{url}', $url, $message);

		return $message;
	}
}