<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\HttpException;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Application\S2bBundle\Entity\Repo;
use Application\S2bBundle\Entity\User;
use Application\S2bBundle\Github;
use Symfony\Components\Console\Output\NullOutput as Output;

class BundleController extends Controller
{

    public function listLastCreatedAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('createdAt', 5);
        return $this->render('S2bBundle:Repo:smallList', array('repos' => $repos));
    }

    public function listLastUpdatedAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('lastCommitAt', 5);
        return $this->render('S2bBundle:Repo:smallList', array('repos' => $repos));
    }

    public function listPopularAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('nbFollowers', 5);
        return $this->render('S2bBundle:Repo:smallList', array('repos' => $repos));
    }

    public function listBestScoreAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('score', 5);
        return $this->render('S2bBundle:Repo:smallList', array('repos' => $repos));
    }

    public function addAction()
    {
        $url = $this['request']->get('url');

        if(preg_match('#^http://github.com/(\w+)/(\w+Bundle).*$#', $url, $match)) {
            $repo = $this->addRepo($match[1], $match[2]);
            if($repo) {
                return $this->redirect($this->generateUrl('repo_show', array('username' => $repo->getUsername(), 'name' => $repo->getName())));
            }
        }

        return $this->forward('S2bBundle:Repo:list', array('sort' => 'score'));
    }

    protected function addRepo($username, $name)
    {
        $repo = $this->getRepoRepository()->findOneByUsernameAndName($username, $name);
        if($repo) {
            return $repo;
        }
        $githubRepo = new Github\Repo(new \phpGithubApi(), new Output());

        if(!$repo = $githubRepo->updateInfos(Repo::create($username.'/'.$name))) {
            return false;
        }
        if(!$repo = $githubRepo->update($repo)) {
            return false;
        }
        
        if(!$user = $this->getUserRepository()->findOneByName($username)) {
            $githubUser = new Github\User(new \phpGithubApi(), new Output());
            if(!$user = $githubUser->import($username)) {
                return false;
            }
        }
        $repo->setUser($user);

        $validator = $this->container->getValidatorService();
        if($validator->validate($repo)->count()) {
            return false;
        }
        $user->addRepo($repo);
        $githubRepo->update($repo);
        $dm = $this->container->getDoctrine_Orm_DefaultEntityManagerService();
        $dm->persist($repo);
        $dm->persist($user);
        $dm->flush();

        return $repo;
    }

    protected function getBundleRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entity\User');
    }

}
