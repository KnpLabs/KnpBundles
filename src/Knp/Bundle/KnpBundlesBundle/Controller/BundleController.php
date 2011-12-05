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

class BundleController extends Controller
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

        $bundles = $this->getRepository('Bundle')->search($query);

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        return $this->render('KnpBundlesBundle:Bundle:searchResults.'.$format.'.twig', array(
            'query'         => $query,
            'bundles'       => $bundles,
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

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $this->highlightMenu($bundle instanceof Bundle);

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->render('KnpBundlesBundle:Bundle:show.'.$format.'.twig', array(
            'bundle'        => $bundle,
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

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $sortField = $this->sortFields[$sort];
        
        if ('html' === $format) {
            $query = $this->getRepository('Bundle')->queryAllWithUsersAndContributorsSortedBy($sortField);
            $bundles = $this->getPaginator($query, $this->get('request')->query->get('page', 1));
        } else {
            $bundles = $this->getRepository('Bundle')->findAllWithUsersAndContributorsSortedBy($sortField);
        }

        $this->highlightMenu();

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'       => $bundles,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function evolutionAction()
    {
        $bundlesitory = $this->getRepository('Score'); 
        $sums = $bundlesitory->getScoreSumEvolution();
        $counts = $bundlesitory->getScoreCountEvolution();

        return $this->render('KnpBundlesBundle:Bundle:evolution.html.twig', array(
            'score_sums'      => $sums,
            'score_counts'    => $counts,
        ));
    }

    public function listLatestAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 50);

        $format = $this->get('request')->query->get('format', 'atom');
        if (!in_array($format, array('atom'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

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
            return $this->redirect('/login');
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
        } else {
            $bundle = '';
            $error = false;
            $errorMessage = '';
        }

        $data = array('bundle' => $bundle, 'error' => $error, 'errorMessage' => $errorMessage);

        return $this->render('KnpBundlesBundle:Bundle:add.html.twig', $data);
    }

    public function changeUsageStatusAction($username, $name)
    {
        if (!$this->userIsLogged()) {
            $this->getRequest()->getSession()->set('redirect_url', $this->generateUrl('bundle_show', array('username' => $username, 'name' => $name)));
            return $this->redirect('/login');
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
            $bundle->updateScore(-1);

            $bundle->getUsers()->removeElement($user);
            $user->getUsedBundles()->removeElement($bundle);
        } else {
            $bundle->updateScore(1);

            $bundle->addRecommender($user);
            $user->addRecommendedBundle($bundle);
            $em->persist($bundle);
            $em->persist($user);
        }

        $em->flush();

        return $this->redirect($this->generateUrl('bundle_show', $params));
    }

    protected function userIsLogged()
    {
        return $this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * Returns the paginator instance configured for the given query and page
     * number
     *
     * @param  Query   $query The query
     * @param  integer $page  The current page number
     *
     * @return Paginator
     */
    protected function getPaginator(Query $query, $page)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            10
        );

        return $pagination;
    }

    protected function getUserRepository()
    {
        return $this->getRepository('User');
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
