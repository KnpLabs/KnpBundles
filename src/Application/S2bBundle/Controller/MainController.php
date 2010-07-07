<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class MainController extends Controller
{

    public function indexAction()
    {
        $nbBundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->count();
        return $this->render('S2bBundle:Main:index', array('nbBundles' => $nbBundles));
    }

    #TODO cache me!
    public function timelineAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
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

        return $this->render('S2bBundle:Main:timeline', array('commits' => $commits));
    }

    public function apiAction()
    {
        $text = file_get_contents(__DIR__.'/../doc/02-Api.markdown');

        return $this->render('S2bBundle:Main:api', array('text' => $text));
    }

    public function notFoundAction()
    {
        $response = $this->render('S2bBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
