<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class MainController extends Controller
{

    public function indexAction()
    {
        $nbBundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->count();
        return $this->render('S2bBundle:Main:index', array('nbBundles' => $nbBundles));
    }

    public function notFoundAction()
    {
        $response = $this->render('S2bBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
