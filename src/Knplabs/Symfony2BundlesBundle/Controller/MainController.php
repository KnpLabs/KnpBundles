<?php

namespace Knplabs\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{

    public function indexAction()
    {
        $nbBundles = $this->getRepository('Bundle')->count();
        $nbProjects = $this->getRepository('Project')->count();
        $nbUsers = $this->getRepository('User')->count();

        return $this->render('KnplabsSymfony2BundlesBundle:Main:index.html.twig', compact('nbBundles', 'nbProjects', 'nbUsers'));
    }

    public function getRankCodeAction()
    {
        try {
            $scoreMethod = new \ReflectionMethod('Knplabs\Symfony2BundlesBundle\Entity\Repo', 'recalculateScore');
            $scoreMethodDefinition = $scoreMethod->getDocComment()."\n";
            $contents = file($scoreMethod->getDeclaringClass()->getFileName());
            for ($i = $scoreMethod->getStartLine()-1; $i < $scoreMethod->getEndLine(); $i++) {
                $scoreMethodDefinition.= $contents[$i];
            }
        } catch (Exception $e) {
            $scoreMethodDefinition = '';
        }

        $response = $this->createResponse($scoreMethodDefinition);
        // TODO: how could we ensure the cache is cleared if the code changes?
        $response->setTtl(3600);
        return $response;
    }

    #TODO cache me!
    public function timelineAction()
    {
        $commits = $this->getRepository('Repo')
            ->getLastCommits(12);

        return $this->render('KnplabsSymfony2BundlesBundle:Main:timeline.html.twig', array('commits' => $commits));
    }

    public function apiAction()
    {
        $text = file_get_contents(__DIR__.'/../Resources/doc/02-Api.markdown');

        return $this->render('KnplabsSymfony2BundlesBundle:Main:api.html.twig', array('text' => $text));
    }

    public function notFoundAction()
    {
        $response = $this->render('KnplabsSymfony2BundlesBundle:Main:notFound');
        $response->setStatusCode(404);
        return $response;
    }

    protected function getRepository($class)
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\\'.$class);
    }
}
