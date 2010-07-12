<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\HttpException;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Application\S2bBundle\Entities\Repo;
use Application\S2bBundle\Entities\Bundle;
use Application\S2bBundle\Entities\Project;
use Application\S2bBundle\Entities\User;
use Application\S2bBundle\Github;
use Symfony\Components\Console\Output\NullOutput as Output;

class RepoController extends Controller
{
    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->getRequest()->get('q')));

        if(empty($query)) {
            return $this->render('S2bBundle:Repo:search');
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

        return $this->render('S2bBundle:Repo:searchResults', array('query' => $query, 'repos' => $repos, 'bundles' => $bundles, 'projects' => $projects, 'callback' => $this->getRequest()->get('callback')));
    }

    public function showAction($username, $name)
    {
        if(!$repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name)) {
            throw new NotFoundHttpException(sprintf('The repo "%s/%s" does not exist', $username, $name));
        }

        return $this->render('S2bBundle:'.$repo->getClass().':show', array('repo' => $repo, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listAction($sort, $class)
    {
        $fields = array(
            'score' => 'score',
            'name' => 'name',
            'lastCommitAt' => 'last updated',
            'createdAt' => 'last created'
        );
        if(!isset($fields[$sort])) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }
        $repos = $this->getRepository($class)->findAllSortedBy($sort);

        return $this->render('S2bBundle:'.$class.':list', array('repos' => $repos, 'sort' => $sort, 'fields' => $fields, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listLatestAction()
    {
        $repos = $this->getRepository('Repo')->findAllSortedBy('createdAt', 50);
        $response = $this->render('S2bBundle:Repo:listLatest', array('repos' => $repos));
        return $response;
    }

    public function addAction()
    {
        $url = $this->getRequest()->get('url');

        if(preg_match('#^http://github.com/(\w+)/(\w+).*$#', $url, $match)) {
            $repo = $this->addRepo($match[1], $match[2]);
            if($repo) {
                return $this->redirect($this->generateUrl('repo_show', array('username' => $repo->getUsername(), 'name' => $repo->getName())));
            }
        }

        return $this->forward('S2bBundle:Bundle:list', array('sort' => 'score'));
    }

    protected function addRepo($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if($repo) {
            return $repo;
        }
        $githubRepo = new Github\Repo(new \phpGithubApi(), new Output());

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
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entities\\'.$class);
    }

}
