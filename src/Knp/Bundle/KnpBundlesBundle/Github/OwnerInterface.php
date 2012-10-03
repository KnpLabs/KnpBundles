<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Knp\Bundle\KnpBundlesBundle\Entity\Owner as EntityOwner;

interface OwnerInterface
{
    /**
     * @param string  $name
     * @param boolean $update
     *
     * @return boolean|EntityOwner
     */
    public function import($name, $update = true);
}
