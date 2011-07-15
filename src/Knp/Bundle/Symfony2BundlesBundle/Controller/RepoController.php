<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Controller;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Output\NullOutput as Output;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knp\Bundle\Symfony2BundlesBundle\Entity\Bundle;
use Zend\Paginator\Paginator;

class RepoController
{
    protected $request;
    protected $em;
    protected $templating;
    protected $httpKernel;
    protected $paginator;
    protected $gitExecutable;
    protected $response;

    protected $sortFields = array(
        'best'          => 'score',
        'updated'       => 'updatedAt',
        'newest'        => 'createdAt'
    );

    protected $sortLegends = array(
        'best'          => 'Best score',
        'updated'       => 'Last update',
        'newest'        => 'Newest'
    );

    public function __construct(Request $request, EngineInterface $templating, EntityManager $em, HttpKernel $httpKernel, Paginator $paginator, $gitExecutable, Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $this->request = $request;
        $this->templating = $templating;
        $this->em = $em;
        $this->httpKernel = $httpKernel;
        $this->paginator = $paginator;
        $this->gitExecutable = $gitExecutable;
        $this->response = $response;
    }

    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->request->query->get('q')));

        if (empty($query)) {
            return $this->templating->renderResponse('KnpSymfony2BundlesBundle:Repo:search.html.twig');
        }

        $repos = $this->getRepository('Repo')->search($query);
        $bundles = $projects = array();
        foreach ($repos as $repo) {
            if ($repo instanceof Bundle) {
                $bundles[] = $repo;
            } else {
                $projects[] = $repo;
            }
        }

        $format = $this->request->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->request->setRequestFormat($format);

        return $this->templating->renderResponse('KnpSymfony2BundlesBundle:Repo:searchResults.'.$format.'.twig', array(
            'query'         => $query,
            'repos'         => $repos,
            'bundles'       => $bundles,
            'projects'      => $projects,
            'callback'      => $this->request->query->get('callback')
        ));
    }

    public function showAction($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if (!$repo) {
            throw new NotFoundHttpException(sprintf('The repo "%s/%s" does not exist', $username, $name));
        }

        $format = $this->request->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->request->setRequestFormat($format);

        return $this->templating->renderResponse('KnpSymfony2BundlesBundle:'.$repo->getClass().':show.'.$format.'.twig', array(
            'repo'          => $repo,
            'callback'      => $this->request->query->get('callback')
        ));
    }

    public function listAction($sort, $class)
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(406, sprintf('%s is not a valid sorting field', $sort));
        }

        $format = $this->request->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->request->setRequestFormat($format);
        
        $sortField = $this->sortFields[$sort];
        
        if ('html' === $format) {
            $query = $this->getRepository($class)->queryAllWithUsersAndContributorsSortedBy($sortField);
            $repos = $this->getPaginator($query, $this->request->query->get('page', 1));
        } else {
            $repos = $this->getRepository($class)->findAllWithUsersAndContributorsSortedBy($sortField);
        }

        return $this->templating->renderResponse('KnpSymfony2BundlesBundle:'.$class.':list.'.$format.'.twig', array(
            'repos'         => $repos,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->request->query->get('callback')
        ));
    }

    public function listLatestAction()
    {
        $repos = $this->getRepository('Repo')->findAllSortedBy('createdAt', 50);

        $format = $this->request->query->get('format', 'atom');
        if (!in_array($format, array('atom'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->request->setRequestFormat($format);

        return $this->templating->renderResponse('KnpSymfony2BundlesBundle:Repo:listLatest.'.$format.'.twig', array(
            'repos'         => $repos,
            'callback'      => $this->request->query->get('callback')
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

    protected function getUserRepository()
    {
        return $this->getRepository('User');
    }

    protected function getRepository($class)
    {
        return $this->em->getRepository('Knp\\Bundle\\Symfony2BundlesBundle\\Entity\\'.$class);
    }

}
