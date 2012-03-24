<?php

namespace Knp\Bundle\KnpBundlesBundle\Indexer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

/**
 * Indexes bundles into Solr.
 *
 * @author Paweł Jędrzejewski <pjedrzejewski@diweb.pl>
 */
class SolrIndexer
{
    /**
     * @var Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    /**
     * @var \Solarium_Client
     */
    protected $solarium;

    /**
     * @param Doctrine\Bundle\DoctrineBundle\Registry $doctrine
     * @param \Solarium_Client                        $solarium
     */
    public function __construct(Registry $doctrine, \Solarium_Client $solarium)
    {
        $this->doctrine = $doctrine;
        $this->solarium = $solarium;
    }

    /**
     * Indexes single bundle.
     *
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     */
    public function indexBundle(Bundle $bundle)
    {
        $update = $this->solarium->createUpdate();
        $document = $update->createDocument();
        $this->updateDocumentFromBundle($document, $bundle, $update->getHelper());
        $update->addDocument($document);
        $update->addCommit();
        $this->solarium->update($update);
        $bundle->setIndexedAt(new \DateTime);

        $this->doctrine->getEntityManager()->flush();
    }

    /**
     * Populates document with bundle data.
     *
     * @param \Solarium_Document_ReadWrite              $document
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @param \Solarium_Query_Helper                    $helper
     */
    private function updateDocumentFromBundle(\Solarium_Document_ReadWrite $document, Bundle $bundle, \Solarium_Query_Helper $helper)
    {
        $document->id = $bundle->getId();
        $document->name = $bundle->getName();
        $document->username = $bundle->getUsername();
        $document->fullName = $bundle->getFullName();
        $document->description = $bundle->getDescription();
        $document->totalScore = $bundle->getScore();
        $document->userGravatarHash = $bundle->getUser()->getGravatarHash();
        $document->lastCommitAt = $helper->formatDate($bundle->getLastCommitAt());

        $keywords = array();
        foreach ($bundle->getKeywords() as $keyword) {
            $keywords[] = $keyword->getValue();
        }
        $document->keywords = $keywords;
    }
}
