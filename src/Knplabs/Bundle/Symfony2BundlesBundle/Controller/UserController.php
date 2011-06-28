<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;

class UserController
{
    protected $request;
    protected $templating;
    protected $em;
    protected $paginator;

    protected $sortFields = array(
        'name'  => 'name',
        'score' => 'score'
    );

    public function __construct(Request $request, EngineInterface $templating, EntityManager $em, Paginator $paginator)
    {
        $this->request = $request;
        $this->templating = $templating;
        $this->em = $em;
        $this->paginator = $paginator;
    }

    public function showAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->attributes->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:User:show.'.$format.'.twig', array(
            'user'      => $user,
            'callback'  => $this->request->query->get('callback')
        ));
    }

    public function listAction($sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->request->attributes->get('_format');

        if ('html' === $format) {
            $query = $this->getUserRepository()->queryAllWithProjectsSortedBy($sort);
            $users = $this->getPaginator($query, $this->request->query->get('page', 1));
        } else {
            $users = $this->getUserRepository()->findAllWithProjectsSortedBy($sort);
        }

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:User:list.'.$format.'.twig', array(
            'users'         => $users,
            'sort'          => $sort,
            'sortFields'    => $this->sortFields,
            'callback'      => $this->request->query->get('callback')
        ));
    }

    public function bundlesAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->attributes->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Bundle:list.'.$format.'.twig', array(
            'repos'     => $user->getBundles(),
            'callback'  => $this->request->query->get('callback')
        ));
    }

    public function projectsAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->attributes->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Project:list.'.$format.'.twig', array(
            'repos'     => $user->getProjects(),
            'callback'  => $this->request->query->get('callback')
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
        $adapter = $this->paginator->getAdapter();
        $adapter->setQuery($query);

        $this->paginator->setCurrentPageNumber($page);

        return $this->paginator;
    }

    protected function getBundleRepository()
    {
        return $this->em->getRepository('Knplabs\Bundle\Symfony2BundlesBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->em->getRepository('Knplabs\Bundle\Symfony2BundlesBundle\Entity\User');
    }
}
