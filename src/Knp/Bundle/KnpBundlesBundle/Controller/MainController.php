<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Main controller
 *
 * @package KnpBundles
 */
class MainController extends Controller
{
    public function getRankCodeAction()
    {
        try {
            $scoreMethod = new \ReflectionMethod('Knp\Bundle\KnpBundlesBundle\Entity\Repo', 'recalculateScore');
            $scoreMethodDefinition = $scoreMethod->getDocComment()."\n";
            $contents = file($scoreMethod->getDeclaringClass()->getFileName());
            for ($i = $scoreMethod->getStartLine()-1; $i < $scoreMethod->getEndLine(); $i++) {
                $scoreMethodDefinition .= $contents[$i];
            }
        } catch (\Exception $e) {
            $scoreMethodDefinition = '';
        }

        $this->response = $this->get('response');
        $this->response->setContent($scoreMethodDefinition);
        $this->response->setStatusCode(200);
        // TODO: how could we ensure the cache is cleared if the code changes?
        $this->response->setTtl(3600);

        return $this->response;
    }

    public function apiAction()
    {
        $text = file_get_contents(__DIR__.'/../Resources/doc/02-Api.markdown');

        return $this->render('KnpBundlesBundle:Main:api.html.twig', array('text' => $text));
    }

    public function notFoundAction()
    {
        $this->get('response')->setStatusCode(404);

        return $this->render('KnpBundlesBundle:Main:notFoundAction.html.twig', array());
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
