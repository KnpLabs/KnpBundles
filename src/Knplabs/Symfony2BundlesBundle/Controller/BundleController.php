<?php

namespace Knplabs\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knplabs\Symfony2BundlesBundle\Entity\Repo;
use Knplabs\Symfony2BundlesBundle\Entity\User;
use Knplabs\Symfony2BundlesBundle\Github;
use Symfony\Component\Console\Output\NullOutput as Output;

class BundleController extends Controller
{
    public function listLastCreatedAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('createdAt', 5);
        return $this->render('KnplabsSymfony2BundlesBundle:Repo:smallList.html.twig', array('repos' => $repos));
    }

    public function listLastUpdatedAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('lastCommitAt', 5);
        return $this->render('KnplabsSymfony2BundlesBundle:Repo:smallList.html.twig', array('repos' => $repos));
    }

    public function listPopularAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('nbFollowers', 5);
        return $this->render('KnplabsSymfony2BundlesBundle:Repo:smallList.html.twig', array('repos' => $repos));
    }

    public function listBestScoreAction()
    {
        $repos = $this->getBundleRepository()->findAllSortedBy('score', 5);
        return $this->render('KnplabsSymfony2BundlesBundle:Repo:smallList.html.twig', array('repos' => $repos));
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

        return $this->forward('KnplabsSymfony2BundlesBundle:Repo:list', array('sort' => 'score'));
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
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('Knplabs\Symfony2BundlesBundle\Entity\User');
    }
}
