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
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Bundle;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Project;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\User;
use Knplabs\Bundle\Symfony2BundlesBundle\Github;
use Knplabs\Bundle\Symfony2BundlesBundle\Git;

class RepoController
{
    protected $request;
    protected $em;
    protected $templating;
    protected $httpKernel;
    protected $reposDir;
    protected $response;

    protected $sortFields = array(
        'score'         => 'score',
        'name'          => 'name',
        'lastCommitAt'  => 'last updated',
        'createdAt'     => 'last created'
    );

    public function __construct(Request $request, EngineInterface $templating, EntityManager $em, HttpKernel $httpKernel, $reposDir, Response $response = null)
    {
        if (null === $response) {
            $response = new Response();
        }

        $this->request = $request;
        $this->templating = $templating;
        $this->em = $em;
        $this->httpKernel = $httpKernel;
        $this->reposDir = $reposDir;
        $this->response = $response;
    }

    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->request->get('q')));

        if(empty($query)) {
            return $this->templating->renderResponse('KnplabsSymfony2Bundles:Repo:search.html.twig');
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

        return $this->templating->renderResponse('KnplabsSymfony2Bundles:Repo:searchResults.' . $format . '.twig', array(
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

        return $this->templating->renderResponse('KnplabsSymfony2Bundles:'.$repo->getClass().':show.' . $format . '.twig', array(
            'repo'          => $repo,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function listAction($sort, $class)
    {
        if(!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $repos = $this->getRepository($class)->findAllSortedBy($sort);

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2Bundles:'.$class.':list.' . $format . '.twig', array(
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

        return $this->templating->renderResponse('KnplabsSymfony2Bundles:Repo:listLatest.' . $format . '.twig', array(
            'repos'         => $repos,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function addAction()
    {
        $url = $this->request->request->get('url');

        if(preg_match('#^http://github.com/([\w-]+)/([\w-]+).*$#', $url, $match)) {
            $repo = $this->addRepo($match[1], $match[2]);
            if($repo) {
                $url = $this->router->generate('repo_show', array(
                    'username'  => $repo->getUsername(),
                    'name'      => $repo->getName()
                ));
                $this->response->setRedirect($url, 302);

                return $this->response;
            }
        }

        $this->httpKernel->forward('KnplabsSymfony2Bundles:Main:index', array('sort' => 'score'));
    }

    protected function addRepo($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if($repo) {
            return $repo;
        }

        $github = new \Github_Client();
        $github->setRequest(new Github\Request());
        $gitRepoManager = new Git\RepoManager($this->reposDir);
        $githubRepo = new Github\Repo($github, new Output(), $gitRepoManager);

        $repo = $githubRepo->update(Repo::create($username.'/'.$name));
        if(!$repo) {
            return false;
        }

        $user = $this->getUserRepository()->findOneByName($username);
        if(!$user) {
            $githubUser = new Github\User(new \Github_Client(), new Output());
            $user = $githubUser->import($username);
            if(!$user) {
                return false;
            }
        }

        $user->addRepo($repo);

        $this->dm->persist($repo);
        $this->dm->persist($user);
        $this->dm->flush();

        return $repo;
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
