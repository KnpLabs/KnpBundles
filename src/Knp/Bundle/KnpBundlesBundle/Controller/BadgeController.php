<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for returning bundle badges
 */
class BadgeController extends BaseController
{
    public function showAction($ownerName, $name, $type = 'long')
    {
        $bundle = $this->getBundleRepository()->findOneByOwnerNameAndName($ownerName, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $ownerName, $name));
        }

        return $this->container->get('knp_bundles.badge_generator')->show($bundle, $type);
    }
}
