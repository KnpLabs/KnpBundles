<?php

namespace Knplabs\Symfony2BundlesBundle\Entity;

/**
 * An Open Source Bundle living on GitHub
 *
 * @orm:Entity(repositoryClass="Knplabs\Symfony2BundlesBundle\Entity\BundleRepository")
 */
class Bundle extends Repo
{
    /**
     * Get the first part of the name, without Bundle
     * @return string
     */
    public function getShortName()
    {
        return preg_replace('/^(.+)Bundle$/', '$1', $this->getName());
    }
}
