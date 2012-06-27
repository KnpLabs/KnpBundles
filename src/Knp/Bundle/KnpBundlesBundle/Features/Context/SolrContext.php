<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\Behat\Context\BehatContext;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Solr context.
 */
class SolrContext extends BehatContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel;
    
    /**
     * @Given /^bundles are indexed$/
     */
    public function bundlesAreIndexed()
    {
        $this->solrIsEnabled();
        $bundles = $this->getContainer()->get('doctrine')->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\Bundle')->findAll();

        $indexer = $this->getContainer()->get('knp_bundles.indexer.solr');
        $indexer->deleteBundlesIndexes();
        foreach ($bundles as $bundle) {
            $indexer->indexBundle($bundle);
        }
    }

    /**
     * @throw Solr HTTP error
     */
    protected function solrIsEnabled()
    {
        $client = $this->getSolariumClient();
        $query = $client->createPing();

        $client->ping($query);
    }

    protected function getSolariumClient()
    {
        return $this->getContainer()->get('solarium.client');
    }

    /**
     * gets container from kernel
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }
    
    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }
}
