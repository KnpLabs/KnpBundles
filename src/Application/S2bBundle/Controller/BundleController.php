<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class BundleController extends Controller
{
    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->getRequest()->get('q')));

        if(empty($query)) {
            return $this->render('S2bBundle:Bundle:search');
        }

        $regex = '.*'.str_replace(' ', '.*', $query).'.*';
        $expressions = array();
        foreach(array('username', 'name', 'description') as $field) {
            $expressions[] = sprintf('this.%s.match(/%s/i)', $field, $regex);
        }
        $reduceFunction = sprintf('function() { return %s; }', implode(' || ', $expressions));

        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->reduce($reduceFunction)
            ->sort('score', 'desc')
            ->execute();
        return $this->render('S2bBundle:Bundle:searchResults', array('query' => $query, 'bundles' => $bundles));
    }

    public function showAction($username, $name)
    {
        $bundle = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->where('username', $username)
            ->where('name', $name)
            ->getSingleResult();
        if(!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }
        return $this->render('S2bBundle:Bundle:show', array('bundle' => $bundle));
    }

    public function listAllAction($sort)
    {
        $query = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle');
        switch($sort) {
            case 'name':
            case 'username':
                $query->sort($sort, 'asc');
                break;
            case 'createdAt':
            case 'lastCommitAt':
            case 'followers':
            case 'forks':
            case 'score':
                $query->sort($sort, 'desc');
                break;
            default:
                throw new NotFoundHttpException($sort.' is not a valid sorting field');
        }

        return $this->render('S2bBundle:Bundle:listAll', array('bundles' => $query->execute(), 'sort' => $sort));
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
            ->sort('lastCommitAt', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listPopularAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('followers', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listBestScoreAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->sort('score', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }
}
