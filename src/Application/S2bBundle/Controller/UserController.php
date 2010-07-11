<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function showAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByNameWithBundles($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }
        $bundles = $user->getBundles();
        $commits = $user->getLastCommits();

        return $this->render('S2bBundle:User:show', array('user' => $user, 'bundles' => $bundles, 'commits' => $commits, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listAllAction()
    {
        $users = $this->getUserRepository()->findAllSortedBy('name');

        return $this->render('S2bBundle:User:listAll', array('users' => $users, 'callback' => $this->getRequest()->get('callback')));
    }

    public function bundlesAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }
        $bundles = $user->getBundles();

        return $this->render('S2bBundle:User:bundles', array('bundles' => $bundles, 'callback' => $this->getRequest()->get('callback')));
    }

    protected function getBundleRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entities\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entities\User');
    }
}
