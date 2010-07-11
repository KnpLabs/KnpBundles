<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;

class MainController extends Controller
{

    public function indexAction()
    {
        $nbBundles = $this->container->getDoctrine_Orm_DefaultEntityManagerService()
            ->getRepository('Application\S2bBundle\Entities\Bundle')
            ->count();
        return $this->render('S2bBundle:Main:index', array('nbBundles' => $nbBundles));
    }

    #TODO cache me!
    public function timelineAction()
    {
        $commits = $this->container->getDoctrine_Orm_DefaultEntityManagerService()
            ->getRepository('Application\S2bBundle\Entities\Bundle')
            ->getLastCommits(5);

        return $this->render('S2bBundle:Main:timeline', array('commits' => $commits));
    }

    public function apiAction()
    {
        $text = file_get_contents(__DIR__.'/../Resources/doc/02-Api.markdown');

        return $this->render('S2bBundle:Main:api', array('text' => $text));
    }

    public function notFoundAction()
    {
        $response = $this->render('S2bBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }
}
