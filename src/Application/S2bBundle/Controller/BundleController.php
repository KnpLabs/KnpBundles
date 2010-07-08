<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Symfony\Components\HttpKernel\Exception\HttpException;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Application\S2bBundle\Document\Bundle;
use Application\S2bBundle\Document\User;
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

        $regex = '.*'.str_replace(' ', '.*', $query).'.*';
        $expressions = array();
        foreach(array('username', 'name', 'description') as $field) {
            $expressions[] = sprintf('this.%s.match(/%s/i)', $field, $regex);
        }
        $reduceFunction = sprintf('function() { return %s; }', implode(' || ', $expressions));

        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->reduce($reduceFunction)
            ->sort('score', 'desc')
            ->execute();

        return $this->render('S2bBundle:Bundle:searchResults', array('query' => $query, 'bundles' => $bundles, 'callback' => $this->getRequest()->get('callback')));
    }

    public function showAction($username, $name)
    {
        $bundle = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->find('Application\S2bBundle\Document\Bundle', array('username' => $username, 'name' => $name))
            ->getSingleResult();
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
        $query = $this->container->getDoctrine_odm_mongodb_documentManagerService()->createQuery('Application\S2bBundle\Document\Bundle');
        $query->sort($sort, 'name' === $sort ? 'asc' : 'desc');

        return $this->render('S2bBundle:Bundle:listAll', array('bundles' => $query->execute(), 'sort' => $sort, 'fields' => $fields, 'callback' => $this->getRequest()->get('callback')));
    }

    public function listLatestAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->sort('createdAt', 'desc')
            ->limit(50)
            ->execute();
        $response = $this->render('S2bBundle:Bundle:listLatest', array('bundles' => $bundles));
        $response->headers->set('Content-Type', 'application/atom+xml');
        return $response;
    }

    public function listLastCreatedAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->sort('createdAt', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listLastUpdatedAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->sort('lastCommitAt', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listPopularAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->sort('followers', 'desc')
            ->limit(5)
            ->execute();
        return $this->render('S2bBundle:Bundle:list', array('bundles' => $bundles));
    }

    public function listBestScoreAction()
    {
        $bundles = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle')
            ->sort('score', 'desc')
            ->limit(5)
            ->execute();
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
        $dm = $this->container->getDoctrine_odm_mongodb_documentManagerService();
        $bundle = $dm->find('Application\S2bBundle\Document\Bundle', array('username' => $username, 'name' => $name))->getSingleResult();
        if($bundle) {
            return $bundle;
        }
        $githubBundle = new Github\Bundle(new \phpGithubApi(), new Output());

        if(!$bundle = $githubBundle->import($username, $name)) {
            return false;
        }
        
        if(!$user = $dm->find('Application\S2bBundle\Document\User', array('name' => $username))->getSingleResult()) {
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
        $dm->persist($bundle);
        $dm->persist($user);
        $dm->flush();

        return $bundle;
    }
}
