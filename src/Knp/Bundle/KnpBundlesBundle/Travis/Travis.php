<?php

namespace Knp\Bundle\KnpBundlesBundle\Travis;

use Symfony\Component\Console\Output\OutputInterface;

use Buzz\Browser;

use Knp\Bundle\KnpBundlesBundle\Entity\Activity;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

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
     * @param  Bundle $bundle
     *
     * @return boolean
     */
    public function update(Bundle $bundle)
    {
        $this->output->write(' Travis status:');

        $response = $this->browser->get('http://travis-ci.org/'.$bundle->getOwnerName().'/'.$bundle->getName().'.json');

        $status = json_decode($response->getContent(), true);
        if (JSON_ERROR_NONE === json_last_error()) {
            $lastBuildAt = new \DateTime();
            $lastBuildAt->setTimestamp(strtotime($status['last_build_finished_at']));

            // Only execute if date of last build is older than last update of bundle
            if ($lastBuildAt < $bundle->getUpdatedAt()) {
                $state = Activity::STATE_UNKNOWN;
                if (0 === $status['last_build_status']) {
                    $bundle->setTravisCiBuildStatus(true);

                    $state = Activity::STATE_OPEN;

                    $this->output->write(' success');
                } elseif (1 === $status['last_build_status']) {
                    $bundle->setTravisCiBuildStatus(false);

                    $state = Activity::STATE_CLOSED;

                    $this->output->write(' failed');
                }

                if (Activity::STATE_UNKNOWN !== $state) {
                    $activity = new Activity();
                    $activity->setType(Activity::ACTIVITY_TYPE_TRAVIS_BUILD);
                    $activity->setState($state);
                    $activity->setBundle($bundle);

                    return true;
                }
            } else {
                $this->output->write(' skipped');

                return true;
            }
        }

        $bundle->setTravisCiBuildStatus(null);
        $this->output->write(' error');

        return false;
    }
}
