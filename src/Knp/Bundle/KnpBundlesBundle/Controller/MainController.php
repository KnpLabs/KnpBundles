<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $translator = $this->get('translator');
        $maxId = $translator->trans('menu.promo.nb');
        $id = rand(0, $maxId - 1);
        $url = $translator->trans('menu.promo.'.$id.'.url');
        $text = $translator->trans('menu.promo.'.$id.'.text');

        return $this->render('KnpBundlesBundle:Main:banner.html.twig', array(
            'url' => $url,
            'id' => $id,
            'text' => $text,
        ));
    }
}
