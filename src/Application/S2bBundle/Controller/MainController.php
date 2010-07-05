<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Application\S2bBundle\Tool\TimeTool;

class MainController extends Controller
{

    public function indexAction()
    {
        $nbBundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->count();
        return $this->render('S2bBundle:Main:index', array('nbBundles' => $nbBundles));
    }

    #TODO cache me!
    public function timelineAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Bundle\BundleStockBundle\Document\Bundle')
            ->execute();
        $commits = array();
        foreach($bundles as $bundle) {
            $commits = array_merge($commits, $bundle->getLastCommits());
        }
        usort($commits, function($a, $b)
        {
            return strtotime($a['committed_date']) < strtotime($b['committed_date']);
        });
        $commits = array_slice($commits, 0, 5);
        foreach($commits as $index => $commit) {
            $commits[$index]['ago'] = TimeTool::ago(date_create($commit['committed_date']));
        }

        return $this->render('S2bBundle:Main:timeline', array('commits' => $commits));
    }

    public function notFoundAction()
    {
        $response = $this->render('S2bBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
