<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;
use Knp\Menu\MenuItem;

class UserController extends Controller
{
    protected $sortFields = array(
        'name'          => 'name',
        'best'          => 'score',
    );

    protected $sortLegends = array(
        'name'          => 'users.sort.name',
        'best'          => 'users.sort.best',
    );

    public function showAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $this->get('knp_bundles.menu.main')->getChild('users')->setCurrent(true);

        return $this->render('KnpBundlesBundle:User:show.'.$format.'.twig', array(
            'user'      => $user,
            'callback'  => $this->get('request')->query->get('callback')
        ));
    }

    public function listAction($sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $sortField = $this->sortFields[$sort];
        $this->get('knp_bundles.menu.main')->getChild('users')->setCurrent(true);

        if ('html' === $format) {
            $query = $this->getUserRepository()->queryAllWithProjectsSortedBy($sortField);
            $users = $this->getPaginator($query, $this->get('request')->query->get('page', 1));
        } else {
            $users = $this->getUserRepository()->findAllWithProjectsSortedBy($sortField);
        }

        return $this->render('KnpBundlesBundle:User:list.'.$format.'.twig', array(
            'users'         => $users,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function bundlesAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'repos'     => $user->getBundles(),
            'callback'  => $this->get('request')->query->get('callback')
        ));
    }

    public function projectsAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        return $this->render('KnpBundlesBundle:Project:list.'.$format.'.twig', array(
            'repos'     => $user->getProjects(),
            'callback'  => $this->get('request')->query->get('callback')
        ));
    }

    /**
     * Returns the paginator instance configured for the given query and page
     * number
     *
     * @param  Query   $query The query
     * @param  integer $page  The current page number
     *
     * @return Paginator
     */
    protected function getPaginator(Query $query, $page)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            10
        );

        return $pagination;
    }

    protected function getBundleRepository()
    {
        return $this->get('knp_bundles.entity_manager')->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->get('knp_bundles.entity_manager')->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\User');
    }
}
