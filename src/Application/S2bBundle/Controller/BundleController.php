<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class BundleController extends Controller
{
    public function listAllAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('name', 'asc')
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listLastCreatedAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('createdAt', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listLastUpdatedAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('updatedAt', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listPopularAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('rank', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }
}
