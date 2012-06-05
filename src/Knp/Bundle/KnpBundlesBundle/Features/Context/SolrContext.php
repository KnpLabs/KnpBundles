<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Solr context.
 */
class SolrContext extends BehatContext
{
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
}
