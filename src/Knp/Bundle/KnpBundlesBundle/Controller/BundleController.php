<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Output\NullOutput as Output;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\SolariumAdapter;
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
        'recommended'   => 'nbRecommenders',
    );

    protected $sortLegends = array(
        'trend'         => 'bundles.sort.trend',
        'best'          => 'bundles.sort.best',
        'updated'       => 'bundles.sort.updated',
        'newest'        => 'bundles.sort.newest',
        'recommended'   => 'bundles.sort.recommended',
    );

    public function searchAction(Request $request)
    {
        $query = preg_replace('(\W)', '', trim($request->query->get('q')));

        if (empty($query)) {
            return $this->render('KnpBundlesBundle:Bundle:search.html.twig');
        }

        $solarium = $this->get('solarium.client');
        $select = $solarium->createSelect();
        $escapedQuery = $select->getHelper()->escapePhrase($query);

        $dismax = $select->getDisMax();
        $dismax->setQueryFields(array('name', 'description', 'keywords', 'text', 'username', 'fullName'));
        $select->setQuery($escapedQuery);

        $paginator = new Pagerfanta(new SolariumAdapter($solarium, $select));
        $paginator
            ->setMaxPerPage(10)
            ->setCurrentPage($request->query->get('page', 1))
        ;
        $bundles = $paginator->getCurrentPageResults();

        $format = $this->recognizeRequestFormat($request);

        if ('html' === $format && count($bundles) === 1 && strtolower($bundles[0]['name']) == strtolower($query)) {
            $params = array('username' => $bundles[0]['username'], 'name' => $bundles[0]['name']);

            return $this->redirect($this->generateUrl('bundle_show', $params));
        }

        return $this->render('KnpBundlesBundle:Bundle:searchResults.'.$format.'.twig', array(
            'query'    => urldecode($request->query->get('q')),
            'bundles'  => $bundles,
            'callback' => $request->query->get('callback')
        ));
    }

    public function showAction(Request $request, $username, $name)
    {
        /* @var $bundle Bundle */
        $bundle = $this->getRepository('Bundle')->findOneByUsernameAndName($username, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $username, $name));
        }

        $format = $this->recognizeRequestFormat($request);

        $this->highlightMenu('bundles');

        $user = $this->get('security.context')->getToken()->getUser();

        return $this->render('KnpBundlesBundle:Bundle:show.'.$format.'.twig', array(
            'series'  => array(
            array(
                'name' => 'Score',
                'data' => $bundle->getScores(),
            )
            ),
            'bundle'        => $bundle,
            'score_details' => $bundle->getScoreDetails(),
            'isUsedByUser'  => $user instanceof User && $user->isUsingBundle($bundle),
            'callback'      => $request->query->get('callback')
        ));
    }

    public function listAction(Request $request, $sort)
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(406, sprintf('%s is not a valid sorting field', $sort));
        }

        $format = $this->recognizeRequestFormat($request);

        $sortField = $this->sortFields[$sort];

        $query   = $this->getRepository('Bundle')->queryAllWithUsersAndContributorsSortedBy($sortField);
        $bundles = $this->getPaginator($query, $request->query->get('page', 1));
        $users   = $this->getRepository('User')->findAllSortedBy('createdAt', 20);

        $this->highlightMenu('bundles');

        $response = $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'series'  => array(
                array(
                    'name' => 'New bundles',
                    'data' => $this->getRepository('Bundle')->getBundlesCountEvolution(5),
                )
            ),
            'bundles'     => $bundles,
            'users'       => $users,
            'sort'        => $sort,
            'sortLegends' => $this->sortLegends,
            'callback'    => $request->query->get('callback')
        ));

        // caching
        $response->setPublic();
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function evolutionAction()
    {
        return $this->render('KnpBundlesBundle:Bundle:evolution.html.twig', array(
            'series'  => array(
                array(
                    'name' => 'Developers',
                    'data' => $this->getRepository('User')->getUsersCountEvolution(),
                ),
                array(
                    'name' => 'Bundles updated',
                    'data' => $this->getRepository('Score')->getScoreCountEvolution(),
                )
            ),
            'bundles' => $this->getRepository('Bundle')->count(),
            'users'   => $this->getRepository('User')->count()
        ));
    }

    public function listLatestAction(Request $request)
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 'desc', 50);

        $format  = $this->recognizeRequestFormat($request, array('atom'), 'atom');

        return $this->render('KnpBundlesBundle:Bundle:listLatest.'.$format.'.twig', array(
            'bundles'  => $bundles,
            'callback' => $request->query->get('callback')
        ));
    }

    public function addAction(Request $request)
    {
        $error  = false;
        $bundle = $request->request->get('bundle');
        if (null === $bundle) {
            $error   = true;
            $message = 'Please enter a valid GitHub repo name (e.g. KnpLabs/KnpBundles).';
        }

        if (!$error) {
            $bundle = str_replace(array('http://github.com', 'https://github.com', '.git'), '', $bundle);
            if (preg_match('/^[a-z0-9-]+\/[a-z0-9-\.]+$/i', trim($bundle, '/'))) {
                list($username, $name) = explode('/', $bundle);

                $url = $this->generateUrl('bundle_show', array('username' => $username, 'name' => $name));
                if ($this->getRepository('Bundle')->findOneByUsernameAndName($username, $name)) {
                    if (!$request->isXmlHttpRequest()) {
                        return $this->redirect($url);
                    } else {
                        $error   = true;
                        $message = 'Specified bundle already <a href="'.$url.'">exists</a> at KnpBundles.com!';
                    }
                }

                if (!$error) {
                    $updater = $this->get('knp_bundles.updater');
                    $updater->setUp();
                    try {
                        $updater->addBundle($bundle, false);

                        if (!$request->isXmlHttpRequest()) {
                            return $this->redirect($url);
                        }
                        $message = '<strong>Hey, friend!</strong> Thanks for adding <a href="'.$url.'">your bundle</a> to our database!';
                    } catch (UserNotFoundException $e) {
                        $error = true;
                        $message = 'Specified user was not found on GitHub.';
                    }
                }
            } else {
                $error = true;
                $message = 'Please enter a valid GitHub repo name (e.g. KnpLabs/KnpBundles).';
            }
        }

        if (!$request->isXmlHttpRequest()) {
            $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 'desc', 5);

            return $this->render('KnpBundlesBundle:Bundle:add.html.twig', array(
                'bundle'       => $bundle,
                'bundles'      => $bundles,
                'error'        => $error,
                'errorMessage' => $message
            ));
        }

        return new JsonResponse(array(
            'message' => $message
        ), $error ? 400 : 201);
    }

    public function changeUsageStatusAction($username, $name)
    {
        /* @var $bundle Bundle */
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

            $bundle->removeRecommender($user);
        } else {
            $bundle->updateScore(5);

            $bundle->addRecommender($user);
        }

        $em->persist($bundle);
        $em->flush();

        return $this->redirect($this->generateUrl('bundle_show', $params));
    }

    public function searchByKeywordAction(Request $request, $slug)
    {
        $query   = $this->getRepository('Bundle')->queryByKeywordSlug($slug);
        $bundles = $this->getPaginator($query, $request->query->get('page', 1));

        $this->highlightMenu('bundles');

        $response = $this->render('KnpBundlesBundle:Bundle:searchByKeywordResults.html.twig', array(
            'bundles'     => $bundles,
            'keywordSlug' => $slug
        ));

        // caching
        $response->setPublic();
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function settingsAction(Request $request, $id)
    {
        /* @var $bundle Bundle */
        $bundle = $this->getRepository('Bundle')->find($id);
        if (!$bundle) {
            throw new NotFoundHttpException('The bundle does not exist.');
        }

        // Save only if sender is owner of bundle
        if ((null !== $user = $this->get('security.context')->getToken()->getUser()) && $bundle->isOwnerOrContributor($user)) {
            $state = $request->request->get('state', Bundle::STATE_UNKNOWN);

            $bundle->setState($state);

            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($bundle);
            $em->flush();

            $request->getSession()->setFlash('notice', sprintf('Bundle status was successful changed to: %s', $state));
        }

        return $this->redirect($this->generateUrl('bundle_show', array('username' => $bundle->getUserName(), 'name' => $bundle->getName())));
    }
}
