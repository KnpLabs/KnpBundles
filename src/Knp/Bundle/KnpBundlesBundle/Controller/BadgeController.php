<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller for returning bundle badges
 *
 */
class BadgeController extends BaseController
{
    function getBadgeAction()
    {
        // Convert image to string
        $image = imagecreatefrompng('http://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/PNG_transparency_demonstration_2.png/240px-PNG_transparency_demonstration_2.png');
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