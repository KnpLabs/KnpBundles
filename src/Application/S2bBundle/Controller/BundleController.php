<?php

namespace Application\S2bBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\HttpKernel\Exception\HttpException;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Application\S2bBundle\Entities\Bundle;
use Application\S2bBundle\Entities\User;
use Application\S2bBundle\Github;
use Symfony\Components\Console\Output\NullOutput as Output;

class BundleController extends Controller
{
    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->getRequest()->get('q')));

        if(empty($query)) {
            return $this->render('S2bBundle:Bundle:search');
        }

        $bundles = $this->getBundleRepository()->search($query);

        return $this->render('S2bBundle:Bundle:searchResults', array('query' => $query, 'bundles' => $bundles, 'callback' => $this->getRequest()->get('callback')));
    }

    public function showAction($username, $name)
    {
        $bundle = $this->getBundleRepository()->findOneByUsernameAndName($username, $name);

        if(!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }
        $commits = $bundle->getLastCommits();

        return $this->render('S2bBundle:Bundle:show', array('bundle' => $bundle, 'commits' => $commits, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listAllAction($sort)
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
        $bundles = $this->getBundleRepository()->findAllSortedBy($sort);

        return $this->render('S2bBundle:Bundle:listAll', array('bundles' => $bundles, 'sort' => $sort, 'fields' => $fields, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listLatestAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('createdAt', 50);
        $response = $this->render('S2bBundle:Bundle:listLatest', array('bundles' => $bundles));
        return $response;
    }

    public function listLastCreatedAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('createdAt', 5);
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listLastUpdatedAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('lastCommitAt', 5);
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listPopularAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('nbFollowers', 5);
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listBestScoreAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('score', 5);
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
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

    protected function getBundleRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entities\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->container->getDoctrine_Orm_DefaultEntityManagerService()->getRepository('Application\S2bBundle\Entities\User');
    }

}
