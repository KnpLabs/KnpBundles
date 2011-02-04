<?php

namespace Knplabs\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knplabs\Symfony2BundlesBundle\Entity\Repo;
use Knplabs\Symfony2BundlesBundle\Entity\Bundle;
use Knplabs\Symfony2BundlesBundle\Entity\Project;
use Knplabs\Symfony2BundlesBundle\Entity\User;
use Knplabs\Symfony2BundlesBundle\Github;
use Knplabs\Symfony2BundlesBundle\Git;
use Symfony\Component\Console\Output\NullOutput as Output;

class RepoController extends Controller
{
    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->get('request')->get('q')));

        if(empty($query)) {
            return $this->render('KnplabsSymfony2BundlesBundle:Repo:search.html.twig');
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

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:Repo:searchResults.' . $format . '.twig', array(
            'query'     => $query,
            'repos'     => $repos,
            'bundles'   => $bundles,
            'projects'  => $projects,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function showAction($username, $name)
    {
        if(!$repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name)) {
            throw new NotFoundHttpException(sprintf('The repo "%s/%s" does not exist', $username, $name));
        }

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:'.$repo->getClass().':show.' . $format . '.twig', array(
            'repo'      => $repo,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function listAction($sort, $class)
    {
        $fields = array(
            'score'         => 'score',
            'name'          => 'name',
            'lastCommitAt'  => 'last updated',
            'createdAt'     => 'last created'
        );

        if(!isset($fields[$sort])) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $repos = $this->getRepository($class)->findAllSortedBy($sort);

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:'.$class.':list.' . $format . '.twig', array(
            'repos'     => $repos,
            'sort'      => $sort,
            'fields'    => $fields,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function listLatestAction()
    {
        $repos = $this->getRepository('Repo')->findAllSortedBy('createdAt', 50);

        $format = $this->get('request')->get('_format');

        return $this->render('KnplabsSymfony2BundlesBundle:Repo:listLatest.' . $format . '.twig', array(
            'repos'     => $repos,
            'callback'  => $this->get('request')->get('callback')
        ));
    }

    public function addAction()
    {
        $url = $this->get('request')->request->get('url');

        if(preg_match('#^http://github.com/([\w-]+)/([\w-]+).*$#', $url, $match)) {
            $repo = $this->addRepo($match[1], $match[2]);
            if($repo) {
                return $this->redirect($this->generateUrl('repo_show', array('username' => $repo->getUsername(), 'name' => $repo->getName())));
            }
        }

        return $this->forward('KnplabsSymfony2BundlesBundle:Main:index', array('sort' => 'score'));
    }

    protected function addRepo($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if($repo) {
            return $repo;
        }
        $github = new \phpGithubApi();
        $github->setRequest(new Github\Request());
        $gitRepoDir = $this->container->getParameter('kernel.cache_dir').'/repos';
        $gitRepoManager = new Git\RepoManager($gitRepoDir);
        $githubRepo = new Github\Repo($github, new Output(), $gitRepoManager);

        if(!$repo = $githubRepo->update(Repo::create($username.'/'.$name))) {
            return false;
        }

        if(!$user = $this->getUserRepository()->findOneByName($username)) {
            $githubUser = new Github\User(new \phpGithubApi(), new Output());
            if(!$user = $githubUser->import($username)) {
                return false;
            }
        }
        $user->addRepo($repo);
        $dm = $this->container->getDoctrine_Orm_DefaultEntityManagerService();
        $dm->persist($repo);
        $dm->persist($user);
        $dm->flush();

        return $repo;
    }

    protected function getUserRepository()
    {
        return $this->getRepository('User');
    }

    protected function getRepository($class)
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\\'.$class);
    }

}
