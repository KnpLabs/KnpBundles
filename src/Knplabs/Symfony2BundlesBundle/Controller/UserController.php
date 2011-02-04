<?php

namespace Knplabs\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function showAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:User:show.' . $format . '.twig', array(
            'user'      => $user,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function listAction()
    {
        $users = $this->getUserRepository()->findAllWithProjectsSortedBy('score');

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:User:list.' . $format . '.twig', array(
            'users'     => $users,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function bundlesAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:Bundle:list.' . $format . '.twig', array(
            'repos'     => $user->getBundles(),
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function projectsAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:Project:list.' . $format . '.twig', array(
            'repos'     => $user->getProjects(),
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    protected function getBundleRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\User');
    }
}
