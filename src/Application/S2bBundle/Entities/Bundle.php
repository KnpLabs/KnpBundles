<?php

namespace Application\S2bBundle\Entities;

/**
 * An Open Source Bundle living on GitHub
 *
 * @Entity(repositoryClass="Application\S2bBundle\Entities\BundleRepository")
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
