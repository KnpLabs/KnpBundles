<?php

namespace Knp\Bundle\KnpBundlesBundle\Travis;

use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

use Buzz\Browser;

/*
 * This class is very simple and stupid - it uses curl for getting data
 * from travis.
 *
 * It should use some Travis client API - when someone will write it.
 */

/**
 * Updates repo based on status from travis.
 */
class Travis
{
    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Browser
     */
    private $browser;

    /**
     * @param OutputInterface $output
     * @param Browser         $browser
     */
    public function __construct(OutputInterface $output, Browser $browser)
    {
        $this->output  = $output;
        $this->browser = $browser;
    }

    /**
     * Updates repo based on status from travis.
     *
     * @param  Bundle $repo
     *
     * @return boolean
     */
    public function update(Bundle $repo)
    {
        $this->output->write(' Travis status:');

        $status = $this->getTravisData($repo->getUsername().'/'.$repo->getName());

        if (!$status) {
            $repo->setTravisCiBuildStatus(null);
            $this->output->write(' error');

            return false;
        }

        switch ($status['last_build_status']) {
            case 0:
                $repo->setTravisCiBuildStatus(true);
                $this->output->write(' success');
                break;
            case 1:
                $repo->setTravisCiBuildStatus(false);
                $this->output->write(' failed');
                break;
            default:
                $repo->setTravisCiBuildStatus(null);
                $this->output->write(' error');
                break;
        }

        return true;
    }

    /**
     * Return data from Travis
     *
     * @param string $url
     *
     * @return array
     */
    private function getTravisData($url)
    {
        $client = $this->browser->getClient();
        $client->setVerifyPeer(false);
        $client->setTimeout(30);

        if ($client instanceof \Buzz\Client\Curl) {
            $client->setOption(CURLOPT_USERAGENT, 'KnpBundles.com Bot');
        }

        $this->browser->setClient($client);

        $response = $this->browser->get($url);

        return json_decode($response->getContent(), true);
    }
}
