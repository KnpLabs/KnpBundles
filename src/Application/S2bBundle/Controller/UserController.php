<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    public function showAction($name)
    {
        $user = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->find('Application\S2bBundle\Document\User', array('name' => $name))
            ->getSingleResult();
        if(!$user) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $username));
        }
        $bundles = $user->getBundles();
        $commits = array();
        foreach($bundles as $bundle) {
            $commits = array_merge($commits, $bundle->getLastCommits());
        }
        usort($commits, function($a, $b)
        {
            return strtotime($a['committed_date']) < strtotime($b['committed_date']);
        });
        $commits = array_slice($commits, 0, 5);

        return $this->render('S2bBundle:User:show', array('user' => $user, 'bundles' => $bundles, 'commits' => $commits));
    }

    public function listAllAction()
    {
        $query = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\User')
            ->sort('name', 'asc');

        return $this->render('S2bBundle:User:listAll', array('users' => $query->execute()));
    }
}
