<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\SolariumAdapter;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;

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
        $format = $request->getRequestFormat();
        $query  = trim($request->query->get('q'));

        if (empty($query)) {
            if ('json' === $format) {
                return new JsonResponse(array('status' => 'error', 'message' => 'Missing or too short search query, example: ?q=example'), 400);
            }

            return $this->render('KnpBundlesBundle:Bundle:search.html.twig');
        }

        /** @var $solarium \Solarium_Client */
        $solarium = $this->get('solarium.client');

        $select = $solarium->createSelect();

        $escapedQuery = $select->getHelper()->escapeTerm($query);

        $dismax = $select->getDisMax();
        $dismax->setQueryFields(array('name^2', 'ownerName', 'fullName^1.5', 'description', 'keywords', 'text', 'text_ngram'));
        $dismax->setPhraseFields(array('description^30'));
        $dismax->setQueryParser('edismax');

        $select->setQuery($escapedQuery);

        $paginator = new Pagerfanta(new SolariumAdapter($solarium, $select));
        $paginator
            ->setMaxPerPage(10)
            ->setCurrentPage($request->query->get('page', 1), false, true)
        ;

        if (1 === $paginator->getNbResults()) {
            $first = $paginator->getCurrentPageResults()->getIterator()->current();
            if (strtolower($first['name']) == strtolower($query)) {
                return $this->redirect($this->generateUrl('bundle_show', array('ownerName' => $first['ownerName'], 'name' => $first['name'], '_format' => $format)));
            }
        }

        if ('json' === $format) {
            $result = array(
                'results' => array(),
                'total'   => $paginator->getNbResults(),
            );

            foreach ($paginator as $bundle) {
                $result['results'][] = array(
                    'name'        => $bundle->fullName,
                    'description' => $bundle->description ?: '',
                    'state'       => $bundle->state,
                    'score'       => $bundle->totalScore,
                    'url'         => $this->generateUrl('bundle_show', array('ownerName' => $bundle->ownerName, 'name' => $bundle->name), true)
                );
            }

            if ($paginator->hasPreviousPage()) {
                $result['prev'] = $this->generateUrl('search', array(
                    'q'       => urldecode($query),
                    'page'    => $paginator->getPreviousPage(),
                    '_format' => 'json',
                ), true);
            }

            if ($paginator->hasNextPage()) {
                $result['next'] = $this->generateUrl('search', array(
                    'q'       => urldecode($query),
                    'page'    => $paginator->getNextPage(),
                    '_format' => 'json',
                ), true);
            }

            return new JsonResponse($result);
        }

        return $this->render('KnpBundlesBundle:Bundle:searchResults.html.twig', array(
            'query'     => urldecode($query),
            'bundles'   => $paginator
        ));
    }

    public function showAction(Request $request, $ownerName, $name)
    {
        $format = $request->getRequestFormat();

        /* @var $bundle Bundle */
        $bundle = $this->getRepository('Bundle')->findOneByOwnerNameAndName($ownerName, $name);
        if (!$bundle) {
            if ('json' === $format) {
                return new JsonResponse(array('status' => 'error', 'message' => 'Bundle not found.'), 404);
            }

            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $ownerName, $name));
        }

        if ('json' === $format) {
            return new JsonResponse($bundle->toBigArray());
        }

        $this->highlightMenu('bundles');

        $owner = $this->get('security.context')->getToken()->getUser();

        $scoresNumber = $this->container->getParameter('knp_bundles.bundle.graph.view_page.scores_number');

        return $this->render('KnpBundlesBundle:Bundle:show.html.twig', array(
            'series'  => array(
                array(
                    'name' => 'Score',
                    'data' => $bundle->getScores($scoresNumber),
                )
            ),
            'bundle'            => $bundle,
            'score_details'     => $bundle->getScoreDetails(),
            'isUsedByDeveloper' => $owner instanceof Developer && $owner->isUsingBundle($bundle)
        ));
    }

    public function listAction(Request $request, $sort)
    {
        $format = $request->getRequestFormat();

        if (!array_key_exists($sort, $this->sortFields)) {
            $msg = sprintf('%s is not a valid sorting field', $sort);
            if ('json' === $format) {
                return new JsonResponse(array('status' => 'error', 'message' => $msg), 406);
            }

            throw new HttpException(406, $msg);
        }

        $sortField = $this->sortFields[$sort];

        $query     = $this->getRepository('Bundle')->queryAllWithOwnersAndContributorsSortedBy($sortField);
        $paginator = $this->getPaginator($query, $request->query->get('page', 1));

        if ('json' === $format) {
            $result = array(
                'results' => array(),
                'total'   => $paginator->getNbResults(),
            );

            foreach ($paginator as $bundle) {
                $result['results'][] = $bundle->toSmallArray() + array(
                    'url' => $this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName()), true)
                );
            }

            if ($paginator->hasPreviousPage()) {
                $result['prev'] = $this->generateUrl('bundle_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getPreviousPage(),
                    '_format' => 'json',
                ), true);
            }

            if ($paginator->hasNextPage()) {
                $result['next'] = $this->generateUrl('bundle_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getNextPage(),
                    '_format' => 'json',
                ), true);
            }

            return new JsonResponse($result);
        }

        $this->highlightMenu('bundles');

        $owners = $this->getRepository('Developer')->findAllSortedBy('createdAt', 20);

        $graphPeriod = $this->container->getParameter('knp_bundles.bundle.graph.main_page.period');

        $response = $this->render('KnpBundlesBundle:Bundle:list.html.twig', array(
            'series'  => array(
                array(
                    'name' => 'New bundles',
                    'data' => $this->getRepository('Bundle')->getEvolutionCounts($graphPeriod),
                )
            ),
            'bundles'     => $paginator,
            'developers'  => $owners,
            'sort'        => $sort,
            'sortLegends' => $this->sortLegends
        ));

        // caching
        $response->setPublic();
        $response->setSharedMaxAge(600);

        return $response;
    }

    public function evolutionAction()
    {
        $period = $this->container->getParameter('knp_bundles.evolution.period');

        return $this->render('KnpBundlesBundle:Bundle:evolution.html.twig', array(
            'evolution'  => array(
                array(
                    'name' => 'Bundles updated',
                    'data' => $this->getRepository('Score')->getEvolutionCounts($period),
                )
            ),

            'recentItems'  => array(
                array(
                    'name' => 'New bundles',
                    'data' => $this->getRepository('Bundle')->getEvolutionCounts($period),
                ),
                array(
                    'name' => 'New developers',
                    'data' => $this->getRepository('Developer')->getEvolutionCounts($period),
                ),
                array(
                    'name' => 'New organizations',
                    'data' => $this->getRepository('Organization')->getEvolutionCounts($period),
                )
            ),

            'bundles'       => $this->getRepository('Bundle')->count(),
            'developers'    => $this->getRepository('Developer')->count(),
            'organizations' => $this->getRepository('Organization')->count()
        ));
    }

    public function listLatestAction()
    {
        $bundles = $this->getRepository('Bundle')->findAllSortedBy('createdAt', 'desc', 50);

        return $this->render('KnpBundlesBundle:Bundle:listLatest.atom.twig', array(
            'bundles'  => $bundles
        ));
    }

    public function addAction(Request $request)
    {
        $error   = false;
        $message = '';
        $bundle  = $request->request->get('bundle');
        if (($request->isXmlHttpRequest() || 'POST' === $request->getMethod()) && null === $bundle) {
            $error   = true;
            $message = 'Please enter a valid GitHub repo name (e.g. KnpLabs/KnpBundles).';
        }

        if (!$error && ($request->isXmlHttpRequest() || 'POST' === $request->getMethod())) {
            $bundle = trim(str_replace(array('http://github.com', 'https://github.com', '.git'), '', $bundle), '/');
            if (preg_match('/^[a-z0-9-]+\/[a-z0-9-\.]+$/i', $bundle)) {
                list($ownerName, $name) = explode('/', $bundle);

                $url = $this->generateUrl('bundle_show', array('ownerName' => $ownerName, 'name' => $name));
                if ($this->getRepository('Bundle')->findOneBy(array('ownerName' => $ownerName, 'name' => $name))) {
                    if (!$request->isXmlHttpRequest()) {
                        return $this->redirect($url);
                    }

                    $error   = true;
                    $message = 'Specified bundle already <a href="'.$url.'">exists</a> at KnpBundles.com!';
                }

                if (!$error) {
                    /** @var $updater \Knp\Bundle\KnpBundlesBundle\Updater\Updater */
                    $updater = $this->get('knp_bundles.updater');
                    $updater->setUp();
                    try {
                        $valid = $updater->addBundle($bundle, false, true);

                        if ($valid) {
                            if (!$request->isXmlHttpRequest()) {
                                return $this->redirect($url);
                            }
                            $message = '<strong>Hey, friend!</strong> Thanks for adding <a href="'.$url.'">your bundle</a> to our database!';
                        } else {
                            $error   = true;
                            $message = 'Specified repo is not valid Symfony2 bundle!';
                        }
                    } catch (UserNotFoundException $e) {
                        $error   = true;
                        $message = 'Specified user was not found on GitHub.';
                    }
                }
            } else {
                $error   = true;
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

    public function changeUsageStatusAction($ownerName, $name)
    {
        /* @var $bundle Bundle */
        $bundle = $this->getRepository('Bundle')->findOneByOwnerNameAndName($ownerName, $name);
        if (!$bundle) {
            throw new NotFoundHttpException(sprintf('The bundle "%s/%s" does not exist', $ownerName, $name));
        }

        $params = array('ownerName' => $ownerName, 'name' => $name);

        if (!$owner = $this->get('security.context')->getToken()->getUser()) {
            return $this->redirect($this->generateUrl('bundle_show', $params));
        }
        $em = $this->get('doctrine')->getEntityManager();

        if ($owner->isUsingBundle($bundle)) {
            $bundle->updateScore(-5);

            $bundle->removeRecommender($owner);
        } else {
            $bundle->updateScore(5);

            $bundle->addRecommender($owner);
        }

        $em->persist($bundle);
        $em->flush();

        return $this->redirect($this->generateUrl('bundle_show', $params));
    }

    public function searchByKeywordAction(Request $request, $slug)
    {
        $format    = $request->getRequestFormat();
        $query     = $this->getRepository('Bundle')->queryByKeywordSlug($slug);
        $paginator = $this->getPaginator($query, $request->query->get('page', 1));

        if ('json' === $format) {
            $result = array(
                'results' => array(),
                'total'   => $paginator->getNbResults(),
            );

            foreach ($paginator as $bundle) {
                $result['results'][] = $bundle->toSmallArray() + array(
                    'url' => $this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName()), true)
                );
            }

            if ($paginator->hasPreviousPage()) {
                $result['prev'] = $this->generateUrl('bundle_keyword', array(
                    'page'    => $paginator->getPreviousPage(),
                    '_format' => 'json',
                ), true);
            }

            if ($paginator->hasNextPage()) {
                $result['next'] = $this->generateUrl('bundle_keyword', array(
                    'page'    => $paginator->getNextPage(),
                    '_format' => 'json',
                ), true);
            }

            return new JsonResponse($result);
        }

        $this->highlightMenu('bundles');

        $response = $this->render('KnpBundlesBundle:Bundle:searchByKeywordResults.html.twig', array(
            'bundles'     => $paginator,
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
        if ((null !== $owner = $this->get('security.context')->getToken()->getUser()) && $bundle->isOwnerOrContributor($owner)) {
            $state = $request->request->get('state', Bundle::STATE_UNKNOWN);

            $bundle->setState($state);

            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($bundle);
            $em->flush();

            $request->getSession()->setFlash('notice', sprintf('Bundle status was successful changed to: %s', $state));
        }

        return $this->redirect($this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName())));
    }
}
