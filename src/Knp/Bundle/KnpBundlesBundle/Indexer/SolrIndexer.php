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
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var \Solarium_Client
     */
    protected $solarium;

    /**
     * @param Registry         $doctrine
     * @param \Solarium_Client $solarium
     */
    public function __construct(Registry $doctrine, \Solarium_Client $solarium)
    {
        $this->doctrine = $doctrine;
        $this->solarium = $solarium;
    }

    /**
     * Indexes single bundle.
     *
     * @param Bundle $bundle
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
     * @param \Solarium_Document_ReadWrite $document
     * @param Bundle                       $bundle
     * @param \Solarium_Query_Helper       $helper
     */
    private function updateDocumentFromBundle(\Solarium_Document_ReadWrite $document, Bundle $bundle, \Solarium_Query_Helper $helper)
    {
        $document->setField('id', $bundle->getId());
        $document->setField('name', $bundle->getName());
        $document->setField('ownerName', $bundle->getOwnerName());
        $document->setField('ownerType', $bundle->getOwnerType());
        $document->setField('fullName', $bundle->getFullName());
        $document->setField('description', $bundle->getDescription());
        $document->setField('readme', $bundle->getReadme());
        $document->setField('totalScore', $bundle->getScore());
        $document->setField('state', $bundle->getState());
        $document->setField('avatarUrl', $bundle->getOwner()->getAvatarUrl());
        $document->setField('lastCommitAt', $helper->formatDate($bundle->getLastCommitAt()));
        $document->setField('lastTweetedAt', null !== $bundle->getLastTweetedAt() ? $helper->formatDate($bundle->getLastTweetedAt()) : null);

        $keywords = array();
        foreach ($bundle->getKeywords() as $keyword) {
            $keywords[mb_strtolower($keyword->getValue(), 'UTF-8')] = true;
        }
        $document->setField('keywords', array_keys($keywords));
    }

    /**
     * Delete all bundles from index
     */
    public function deleteBundlesIndexes(Bundle $bundle = null)
    {
        $delete = $this->solarium->createUpdate();
        $delete->addDeleteQuery(null !== $bundle ? $bundle->getFullName() : '*:*');
        $delete->addCommit();

        $this->solarium->update($delete);
    }
}
