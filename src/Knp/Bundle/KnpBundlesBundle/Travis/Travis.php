<?php

namespace Knp\Bundle\KnpBundlesBundle\Travis;

use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Entity;

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
    protected $output = null;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Updates repo based on status from travis.
     * 
     * @param Entity\Repo $repo
     * @return boolean 
     */
    public function update(Entity\Repo $repo)
    {
        $this->output->write(' travis status');

        $status = $this->getTravisDataForRepo($repo);

        if (!$status) {
            $repo->setTravisCiBuildStatus(null);
            $this->output->write(' failed');

            return false;
        }

        switch ($status['last_build_status']) {
            case 0:
                $repo->setTravisCiBuildStatus(true);
                break;
            case 1:
                $repo->setTravisCiBuildStatus(false);
                break;
            default:
                $repo->setTravisCiBuildStatus(null);
                break;        
          }
    }

    /**
     * Get repository status for Repo
     * 
     * @param Entity\Repo $repo
     * @return array
     */
    protected function getTravisDataForRepo(Entity\Repo $repo)
    {
        return $this->getTravisData($repo->getUser()."/".$repo->getUsername());
    }
  
    /**
     * Return data from Travis 
     * 
     * @param string $url
     * @return array
     */
    protected function getTravisData($url)
    {
        $curl = curl_init();

        $curlOptions = array(
            CURLOPT_URL => "http://travis-ci.org/".$url.".json",
            CURLOPT_PORT => 80,
            CURLOPT_USERAGENT => "KnpBundles",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 60
        );
        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}