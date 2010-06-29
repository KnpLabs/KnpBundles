<?php

namespace Application\FrontBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class BundleController extends Controller
{
    public function listAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->execute();
        return $this->render('FrontBundle:Bundle:list', array('bundles' => $bundles));
    }
}
