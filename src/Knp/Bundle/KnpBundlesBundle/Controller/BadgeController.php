<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for returning bundle badges
 *
 */
class BadgeController extends BaseController
{
    public function getBadgeAction($username, $name)
    {
        $bundle = $this->get('doctrine')
            ->getRepository('KnpBundlesBundle:Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        $file = $this->container->getParameter('kernel.cache_dir').'/badges/'.$username.'-'.$name.'.png';
        if (!file_exists($file)) {
            throw new NotFoundHttpException(sprintf('The badge is missing for "%s/%s"', $username, $name));
        }

        return $this->get('igorw_file_serve.response_factory')->create(
            $this->getRelativePath().'/badges/'.$username.'-'.$name.'.png',
            'image/png'
        );
    }

   private function getRelativePath() {
        return str_replace(
            $this->container->getParameter('kernel.root_dir').'/',
            '',
            $this->container->getParameter('kernel.cache_dir')
        );
    }
}