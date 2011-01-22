<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function showAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        return $this->render('S2bBundle:User:show.html.twig', array('user' => $user, 'callback' => $this->get('request')->get('callback')));
    }

    public function listAction()
    {
        $users = $this->getUserRepository()->findAllWithProjectsSortedBy('score');

        return $this->render('S2bBundle:User:list.html.twig', array('users' => $users, 'callback' => $this->get('request')->get('callback')));
    }

    public function bundlesAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        return $this->render('S2bBundle:Bundle:list.html.twig', array('repos' => $user->getBundles(), 'callback' => $this->get('request')->get('callback')));
    }

    public function projectsAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        return $this->render('S2bBundle:Project:list.html.twig', array('repos' => $user->getProjects(), 'callback' => $this->get('request')->get('callback')));
    }

    protected function getBundleRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Application\S2bBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Application\S2bBundle\Entity\User');
    }
}
