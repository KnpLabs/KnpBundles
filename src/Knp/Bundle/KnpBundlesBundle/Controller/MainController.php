<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

/**
 * Main controller
 *
 * @package KnpBundles
 */
class MainController extends BaseController
{
    public function apiAction()
    {
        $text = file_get_contents(__DIR__.'/../Resources/doc/api.markdown');

        return $this->render('KnpBundlesBundle:Main:api.html.twig', array('text' => $text));
    }

    public function symfony2bundlesAction()
    {
        $text = file_get_contents(__DIR__.'/../Resources/doc/symfony2bundles.markdown');

        return $this->render('KnpBundlesBundle:Main:symfony2bundles.html.twig', array('text' => $text));
    }

    public function aboutScoringAction()
    {
        return $this->render('KnpBundlesBundle:Main:aboutScoring.html.twig', array(
            'text' => file_get_contents(__DIR__.'/../Resources/doc/about_scoring.markdown'),
        ));
    }

    public function bannerAction()
    {
        $banner = $this->container->getParameter('banner');

        $banner = str_replace(array('[', ']', '|'), array('', '', ' '), $banner['text']);

        return $this->render('KnpBundlesBundle::layout/promo.html.twig', array(
            'text' => $banner
        ));
    }
}
