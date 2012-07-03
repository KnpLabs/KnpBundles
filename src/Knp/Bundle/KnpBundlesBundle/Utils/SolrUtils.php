<?php

namespace Knp\Bundle\KnpBundlesBundle\Utils;

use Symfony\Component\Process\Process;

class SolrUtils
{
    private $kernel;
    private $solarium;

    public function __construct($solarium, $kernel)
    {
        $this->solarium = $solarium;
        $this->kernel   = $kernel;
    }

    /**
     * Get SOLR pid
     *
     * @return integer
     */
    public function getSolrPid()
    {
        $process = new Process(sprintf('ps aux | grep \\\\%s | grep -v grep | awk \'{ print $2 }\'', $this->buildProperties('| grep \\\\')));
        $process->run();
        $pid = $process->getOutput();

        return (integer) $pid;
    }

    /**
     * @return boolean
     */
    public function isSolrRunning()
    {
        return (boolean) $this->getSolrPid();
    }

    /**
     * Build SOLR start.jar properties
     *
     * @param string $glue
     *
     * @return string
     */
    public function buildProperties($glue = ' ')
    {
        $properties = array();
        foreach ($this->getPropertiesArray() as $key => $property) {
            $properties[] = $key.'='.$property;
        }

        return implode($glue, $properties);
    }

    /**
     * @return array
     */
    private function getPropertiesArray()
    {
        return array(
            '-Djetty.port'     => $this->solarium->getAdapter()->getPort(),
            '-Dsolr.solr.home' => $this->kernel->getBundle('KnpBundlesBundle')->getPath().'/Resources/solr'
        );
    }
}
