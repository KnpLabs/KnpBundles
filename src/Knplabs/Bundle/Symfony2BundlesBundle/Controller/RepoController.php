<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Controller;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Output\NullOutput as Output;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Bundle;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Project;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\User;
use Knplabs\Bundle\Symfony2BundlesBundle\Github;
use Knplabs\Bundle\Symfony2BundlesBundle\Git;
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
        'score'         => 'score',
        'name'          => 'name',
        'lastCommitAt'  => 'last updated',
        'createdAt'     => 'last created'
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
        $query = preg_replace('(\W)', '', trim($this->request->get('q')));

        if(empty($query)) {
            return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Repo:search.html.twig');
        }

        $repos = $this->getRepository('Repo')->search($query);
        $bundles = $projects = array();
        foreach($repos as $repo) {
            if($repo instanceof Bundle) {
                $bundles[] = $repo;
            }
            else {
                $projects[] = $repo;
            }
        }

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Repo:searchResults.' . $format . '.twig', array(
            'query'         => $query,
            'repos'         => $repos,
            'bundles'       => $bundles,
            'projects'      => $projects,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function showAction($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if(!$repo) {
            throw new NotFoundHttpException(sprintf('The repo "%s/%s" does not exist', $username, $name));
        }

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:'.$repo->getClass().':show.' . $format . '.twig', array(
            'repo'          => $repo,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function listAction($sort, $class)
    {
        if(!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->request->get('_format');

        if ('html' === $format) {
            $query = $this->getRepository($class)->queryAllWithUsersAndContributorsSortedBy($sort);
            $repos = $this->getPaginator($query, $this->request->query->get('page', 1));
        } else {
            $repos = $this->getRepository($class)->findAllWithUsersAndContributorsSortedBy($sort);
        }

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:'.$class.':list.' . $format . '.twig', array(
            'repos'         => $repos,
            'sort'          => $sort,
            'sortFields'    => $this->sortFields,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function listLatestAction()
    {
        $repos = $this->getRepository('Repo')->findAllSortedBy('createdAt', 50);

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Repo:listLatest.' . $format . '.twig', array(
            'repos'         => $repos,
            'callback'      => $this->request->get('callback')
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
        return $this->em->getRepository('Knplabs\\Bundle\\Symfony2BundlesBundle\\Entity\\' . $class);
    }

}
