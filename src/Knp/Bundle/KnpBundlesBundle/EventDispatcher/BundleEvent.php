<?php

namespace Knp\Bundle\KnpBundlesBundle\EventDispatcher;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Component\EventDispatcher\Event;
/**
* 
*/
class BundleEvent extends Event
{
    
    const UPDATE_SCORE = 'bundle.update_score';

    /**
     * @var Knp\Bundle\KnpBundlesBundle\Entity\Bundle
     */
    private $bundle;

    /**
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle
     */
    public function __construct(Bundle $bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * @return Knp\Bundle\KnpBundlesBundle\Entity\Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }
}