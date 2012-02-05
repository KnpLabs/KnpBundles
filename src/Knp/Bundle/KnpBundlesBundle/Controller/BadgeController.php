<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for returning bundle badges
 *
 */
class BadgeController extends BaseController
{
    function getBadgeAction($username, $name)
    {
        $bundle = $this->get('doctrine')
            ->getRepository('KnpBundlesBundle:Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        // Convert image to string
        $image = imagecreatefrompng(
            $this->container->getParameter('knp_bundles.badges_upload_dir').'/'.$username.'-'.$name.'.png'
        );
        ob_start();
        imagepng($image);
        $response = ob_get_clean();

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="image.png"'
        );

        return new Response($response, 200, $headers);
    }
}