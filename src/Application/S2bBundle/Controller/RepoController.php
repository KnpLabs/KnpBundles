<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\HttpException;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
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
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 50);
        $response = $this->render('S2bBundle:Repo:listLatest', array('repos' => $repos));
        return $response;
    }

    public function listLastCreatedAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 5);
        return $this->render('S2bBundle:Repo:list', array('repos' => $repos));
    }

    public function listLastUpdatedAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('lastCommitAt', 5);
        return $this->render('S2bBundle:Repo:list', array('repos' => $repos));
    }

    public function listPopularAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('nbFollowers', 5);
        return $this->render('S2bBundle:Repo:list', array('repos' => $repos));
    }

    public function listBestScoreAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('score', 5);
        return $this->render('S2bBundle:Repo:list', array('repos' => $repos));
    }

    public function addAction()
    {
        $url = $this->getRequest()->get('url');

        if(preg_match('#^http://github.com/(\w+)/(\w+Bundle).*$#', $url, $match)) {
            $bundle = $this->addBundle($match[1], $match[2]);
            if($bundle) {
                return $this->redirect($this->generateUrl('bundle_show', array('username' => $bundle->getUsername(), 'name' => $bundle->getName())));
            }
        }

        return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
    }

    protected function addBundle($username, $name)
    {
        $bundle = $this->getBundleRepository()->findOneByUsernameAndName($username, $name);
        if($bundle) {
            return $bundle;
        }
        $githubBundle = new Github\Bundle(new \phpGithubApi(), new Output());

        if(!$bundle = $githubBundle->updateInfos(new Bundle($username.'/'.$name))) {
            return false;
        }
        if(!$bundle = $githubBundle->update($bundle)) {
            return false;
        }
        
        if(!$user = $this->getUserRepository()->findOneByName($username)) {
            $githubUser = new Github\User(new \phpGithubApi(), new Output());
            if(!$user = $githubUser->import($username)) {
                return false;
            }
        }
        $bundle->setUser($user);

        $validator = $this->container->getValidatorService();
        if($validator->validate($bundle)->count()) {
            return false;
        }
        $user->addBundle($bundle);
        $githubBundle->update($bundle);
        $dm = $this->container->getDoctrine_Orm_DefaultEntityManagerService();
        $dm->persist($bundle);
        $dm->persist($user);
        $dm->flush();

        return $bundle;
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
