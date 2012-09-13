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
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param Browser $browser
     */
    public function setBrowser(Browser $browser)
    {
        $client = $browser->getClient();
        $client->setVerifyPeer(false);
        $client->setTimeout(30);

        if ($client instanceof \Buzz\Client\Curl) {
            $client->setOption(CURLOPT_USERAGENT, 'KnpBundles.com Bot');
        }

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

        $response = $this->browser->get('http://travis-ci.org/'.$repo->getOwnerName().'/'.$repo->getName().'.json');

        $status = json_decode($response->getContent(), true);
        if (JSON_ERROR_NONE === json_last_error()) {
            if (0 === $status['last_build_status']) {
                $repo->setTravisCiBuildStatus(true);
                $this->output->write(' success');

                return true;
            }

            if (1 === $status['last_build_status']) {
                $repo->setTravisCiBuildStatus(false);
                $this->output->write(' failed');

                return true;
            }
        }

        $repo->setTravisCiBuildStatus(null);
        $this->output->write(' error');

        return false;
    }
}
