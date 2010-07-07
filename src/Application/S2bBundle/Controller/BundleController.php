<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;
use Symfony\Components\HttpKernel\Exception\NotFoundHttpException;
use Application\S2bBundle\Document\Bundle;
use Application\S2bBundle\Document\User;

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
        return $this->render('S2bBundle:Bundle:searchResults', array('query' => $query, 'bundles' => $bundles));
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

        return $this->render('S2bBundle:Bundle:show', array('bundle' => $bundle, 'commits' => $commits));
    }

    public function listAllAction($sort)
    {
        $query = $this->container->getDoctrine_odm_mongodb_documentManagerService()
            ->createQuery('Application\S2bBundle\Document\Bundle');
        switch($sort) {
            case 'name':
            case 'username':
                $query->sort($sort, 'asc');
                break;
            case 'createdAt':
            case 'lastCommitAt':
            case 'followers':
            case 'forks':
            case 'score':
                $query->sort($sort, 'desc');
                break;
            default:
                throw new NotFoundHttpException($sort.' is not a valid sorting field');
        }

        return $this->render('S2bBundle:Bundle:listAll', array('bundles' => $query->execute(), 'sort' => $sort));
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
        if(!preg_match('#^http://github.com/(\w+)/(\w+Bundle).*$#', $url, $match)) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $username = $match[1];
        $name = $match[2];
        $dm = $this->container->getDoctrine_odm_mongodb_documentManagerService();
        $bundle = $dm->find('Application\S2bBundle\Document\Bundle', array('username' => $username, 'name' => $name))->getSingleResult();
        if($bundle) {
            return $this->redirect($this->generateUrl('bundle_show', array('username' => $username, 'name' => $name)));
        }
        $validator = $this->container->getValidatorService();
        // Require php-github-api
        require_once(__DIR__.'/../../../vendor/php-github-api/lib/phpGitHubApi.php');
        $github = new \phpGithubApi();
        $githubRepo = $github->getRepoApi()->show($username, $name);
        if(!$githubRepo) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $bundle = new Bundle();
        $bundle->fromRepositoryArray($githubRepo);
        $bundle->setIsOnGithub(true);
        $user = $dm->find('Application\S2bBundle\Document\User', array('name' => $name))->getSingleResult();
        if(!$user) {
            $user = new User();
            $user->setName($username);
            $data = $github->getUserApi()->show($user->getName());
            $user->fromUserArray($data);
        }
        $bundle->setUser($user);
        if($validator->validate($bundle)->count()) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $user->addBundle($bundle);
        $dm->persist($bundle);
        $dm->persist($user);
        $commits = $github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'master');
        if(empty($commits)) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $bundle->setLastCommits(array_slice($commits, 0, 5));
        $blobs = $github->getObjectApi()->listBlobs($bundle->getUsername(), $bundle->getName(), 'master');
        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if(isset($blobs[$readmeFilename])) {
                $readmeSha = $blobs[$readmeFilename];
                $readmeText = $github->getObjectApi()->getRawData($bundle->getUsername(), $bundle->getName(), $readmeSha);
                $bundle->setReadme($readmeText);
                break;
            }
        }
        $tags = $github->getRepoApi()->getRepoTags($bundle->getUsername(), $bundle->getName());
        $bundle->setTags(array_keys($tags));
        $bundle->recalculateScore();
        $dm->flush();
        return $this->redirect($this->generateUrl('bundle_show', array('username' => $bundle->getUserName(), 'name' => $bundle->getName())));
    }
}
