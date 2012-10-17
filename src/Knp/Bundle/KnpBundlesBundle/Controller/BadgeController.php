<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for returning bundle badges
 */
class BadgeController extends BaseController
{
    public function showAction($ownerName, $name, $type = 'long')
    {
        $bundle = $this->getRepository('Bundle')->findOneByOwnerNameAndName($ownerName, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $ownerName, $name));
        }

        $filename = $this->container->get('knp_bundles.badge_generator')->show($bundle, $type);

        return $this->container->get('igorw_file_serve.response_factory')->create($filename, 'image/png');
    }
}
