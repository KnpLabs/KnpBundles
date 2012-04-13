<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Output\NullOutput as Output;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\User;
use Knp\Menu\MenuItem;

class BundleController extends BaseController
{
    protected $sortFields = array(
        'trend'         => 'trend1',
        'best'          => 'score',
        'updated'       => 'lastCommitAt',
        'newest'        => 'createdAt',
    );

    protected $sortLegends = array(
        'trend'         => 'bundles.sort.trend',
        'best'          => 'bundles.sort.best',
        'updated'       => 'bundles.sort.updated',
        'newest'        => 'bundles.sort.newest',
    );

    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->get('request')->query->get('q')));

        if (empty($query)) {
            return $this->render('KnpBundlesBundle:Bundle:search.html.twig');
        }

        $solarium = $this->get('solarium.client');
        $select = $solarium->createSelect();
        $escapedQuery = $select->getHelper()->escapePhrase($query);

        $dismax = $select->getDisMax();
        $dismax->setQueryFields(array('name', 'description', 'keywords', 'text', 'username', 'fullName'));
        $select->setQuery($escapedQuery);

        $paginator = $this->get('knp_paginator');
        $bundles = $paginator->paginate(
            array($solarium, $select),
            $this->get('request')->query->get('page', 1),
            10
        );

        $format = $this->recognizeRequestFormat();

        return $this->render('KnpBundlesBundle:Bundle:searchResults.'.$format.'.twig', array(
            'query'         => urldecode($this->get('request')->query->get('q')),
            'bundles'       => $bundles,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function showAction($username, $name)
    {
        $bundle = $this->getRepository('Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        $format = $this->recognizeRequestFormat();

        $this->highlightMenu();

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->render('KnpBundlesBundle:Bundle:show.'.$format.'.twig', array(
            'bundle'        => $bundle,
            'score_details' => $bundle->getScoreDetails(),
            'isUsedByUser'  => $user instanceof User && $user->isUsingBundle($bundle),
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function listAction($sort)
    {
        # crappy hack for oauth return_url
        $session = $this->getRequest()->getSession();

        if (null !== $redirect_url = $session->get('redirect_url', null)) {
            $session->remove('redirect_url');

            return $this->redirect($redirect_url);
        }
        # end hack

        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(406, sprintf('%s is not a valid sorting field', $sort));
        }

        $format = $this->recognizeRequestFormat();

        $sortField = $this->sortFields[$sort];

        if ('html' === $format) {
            $query = $this->getRepository('Bundle')->queryAllWithUsersAndContributorsSortedBy($sortField);
            $bundles = $this->getPaginator($query, $this->get('request')->query->get('page', 1));
        } else {
            $bundles = $this->getRepository('Bundle')->findAllWithUsersAndContributorsSortedBy($sortField);
        }

        $this->highlightMenu();

        $response = $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'       => $bundles,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->get('request')->query->get('callback')
        ));

        // caching
        $response->setPublic();
        $response->setMaxAge(600);

        return $response;
    }

    public function evolutionAction()
    {
        $bundlesitory = $this->getRepository('Score');
        $counts = $bundlesitory->getScoreCountEvolution();

        return $this->render('KnpBundlesBundle:Bundle:evolution.html.twig', array(
            'score_counts'    => $counts,
        ));
    }

    public function listLatestAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 50);

        $format = $this->recognizeRequestFormat(array('atom'), 'atom');

        return $this->render('KnpBundlesBundle:Bundle:listLatest.'.$format.'.twig', array(
            'bundles'       => $bundles,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function addAction(Request $request)
    {
        if (!$this->userIsLogged()) {
            # crappy hack for oauth return url
            $this->getRequest()->getSession()->set('redirect_url', $this->generateUrl('add_bundle'));
            # end hack
            return $this->redirect($this->generateUrl('_login'));
        }

        $error = false;
        $errorMessage = $bundle = '';
        if ($request->request->has('bundle')) {
            $bundle = $request->request->get('bundle');

            if (preg_match('/^[a-z0-9-]+\/[a-z0-9-\.]+$/i', $bundle)) {
                $updater = $this->get('knp_bundles.updater');
                $updater->setUp();
                try {
                    $bundles = $updater->addBundle($bundle, false);

                    $bundleParts = explode('/', $bundle);
                    $params = array('username' => $bundleParts[0], 'name' => $bundleParts[1]);

                    return $this->redirect($this->generateUrl('bundle_show', $params));
                } catch (UserNotFoundException $e) {
                    $error = true;
                    $errorMessage = 'addBundle.userNotFound';
                }
            } else {
                $error = true;
                $errorMessage = 'addBundle.invalidBundleName';
            }
        }

        $data = array('bundle' => $bundle, 'error' => $error, 'errorMessage' => $errorMessage);

        return $this->render('KnpBundlesBundle:Bundle:add.html.twig', $data);
    }

    public function changeUsageStatusAction($username, $name)
    {
        if (!$this->userIsLogged()) {
            $this->getRequest()->getSession()->set('redirect_url', $this->generateUrl('bundle_show', array('username' => $username, 'name' => $name)));

            return $this->redirect($this->generateUrl('_login'));
        }

        $bundle = $this->getRepository('Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        $params = array('username' => $username, 'name' => $name);

        if (!$user = $this->get('security.context')->getToken()->getUser()) {
            return $this->redirect($this->generateUrl('bundle_show', $params));
        }
        $em = $this->get('doctrine')->getEntityManager();

        if ($user->isUsingBundle($bundle)) {
            $bundle->updateScore(-5);

            $bundle->getRecommenders()->removeElement($user);
            $user->getUsedBundles()->removeElement($bundle);
        } else {
            $bundle->updateScore(5);

            $bundle->addRecommender($user);
            $user->addRecommendedBundle($bundle);
            $em->persist($bundle);
            $em->persist($user);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('bundle_show', $params));
    }

    public function toggleFavouriteAction($username, $name)
    {
        if (!$this->userIsLogged()) {
            $this->getRequest()->getSession()->set('redirect_url', $this->generateUrl('bundle_show', array('username' => $username, 'name' => $name)));

            return $this->redirect($this->generateUrl('_login'));
        }

        $bundle = $this->getRepository('Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        $params = array('username' => $username, 'name' => $name);

        if (!$user = $this->get('security.context')->getToken()->getUser()) {
            return $this->redirect($this->generateUrl('bundle_show', $params));
        }
        $em = $this->get('doctrine')->getEntityManager();

        if ($user->hasFavourite($bundle)) {
            $bundle->updateScore(-5);
            $user->removeFavourite($bundle);
        } else {
            $bundle->updateScore(5);
            $user->addFavourite($bundle);
        }

        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('bundle_show', $params));
    }

    public function searchByKeywordAction($slug)
    {
        $query = $this->getRepository('Bundle')->queryByKeywordSlug($slug);
        $bundles = $this->getPaginator($query, $this->get('request')->query->get('page', 1));

        $this->highlightMenu();

        $response = $this->render('KnpBundlesBundle:Bundle:searchByKeywordResults.html.twig', array(
            'bundles'     => $bundles,
            'keywordSlug' => $slug
        ));

        // caching
        $response->setPublic();
        $response->setMaxAge(600);

        return $response;
    }

    public function settingsAction($id)
    {
        $bundle = $this->getRepository('Bundle')->find($id);
        if (!$bundle) {
            throw new NotFoundHttpException('The bundle does not exist.');
        }

        // Save only if sender is owner of bundle
        if ((null !== $user = $this->get('security.context')->getToken()->getUser()) && $bundle->isOwnerOrContributor($user)) {
            $state = $this->getRequest()->request->get('state', Bundle::STATE_UNKNOWN);

            $bundle->setState($state);

            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($bundle);
            $em->flush();

            $this->getRequest()->getSession()->setFlash('notice', sprintf('Bundle status was successful changed to: %s', $state));
        }

        return $this->redirect($this->generateUrl('bundle_show', array('username' => $bundle->getUserName(), 'name' => $bundle->getName())));
    }

    protected function userIsLogged()
    {
        return $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    protected function getRepository($class)
    {
        return $this->get('knp_bundles.entity_manager')->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\'.$class);
    }

    protected function highlightMenu()
    {
        $this->get('knp_bundles.menu.main')->getChild('bundles')->setCurrent(true);
    }
}
