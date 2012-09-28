<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Knp\Bundle\KnpBundlesBundle\Entity\Owner as EntityOwner;

interface OwnerInterface
{
    /**
     * @param string|UserResponseInterface $response
     * @param boolean                      $update
     *
     * @return boolean|EntityOwner
     */
    public function import($response, $update = true);
}
