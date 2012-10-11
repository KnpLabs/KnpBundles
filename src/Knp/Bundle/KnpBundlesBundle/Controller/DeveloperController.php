<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;

class DeveloperController extends BaseController
{
    protected $sortFields = array(
        'name' => 'name',
        'best' => 'score',
    );

    protected $sortLegends = array(
        'name' => 'developers.sort.name',
        'best' => 'developers.sort.best',
    );

    public function userbarAction()
    {
        $response = $this->render('KnpBundlesBundle:Developer:userbar.html.twig');

        // this is private cache (don't cache with shared proxy)
        $response->setPrivate();

        return $response;
    }

    public function showAction(Request $request, $name)
    {
        $format = $request->getRequestFormat();
        /* @var $developer Developer */
        if (!$developer = $this->getRepository('Developer')->findOneByNameWithRepos($name)) {
            if ('json' === $format) {
                return new JsonResponse(array('status' => 'error', 'message' => 'Developer not found.'), 404);
            }

            throw new NotFoundHttpException(sprintf('The developer "%s" does not exist', $name));
        }

        if ('json' === $format) {
            $result = array(
                'name'          => $developer->getName(),
                'email'         => $developer->getEmail(),
                'avatarUrl'     => $developer->getAvatarUrl(),
                'fullName'      => $developer->getFullName(),
                'company'       => $developer->getCompany(),
                'location'      => $developer->getLocation(),
                'blog'          => $developer->getUrl(),
                'bundles'       => array(),
                'lastCommitAt'  => $developer->getLastCommitAt() ? $developer->getLastCommitAt()->getTimestamp() : null,
                'score'         => $developer->getScore()
            );

            /* @var $bundle Bundle */
            foreach ($developer->getBundles() as $bundle) {
                $result['bundles'][] = array(
                    'name'  => $bundle->getFullName(),
                    'state' => $bundle->getState(),
                    'score' => $bundle->getScore(),
                    'url'   => $this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName()), true)
                );
            }

            return new JsonResponse($result);
        }

        $this->highlightMenu('developers');

        return $this->render('KnpBundlesBundle:Developer:show.html.twig', array(
            'developer' => $developer
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

            throw new HttpException($msg, 406);
        }

        $sortField = $this->sortFields[$sort];

        $query = $this->getRepository('Developer')->queryAllWithBundlesSortedBy($sortField);
        $paginator = $this->getPaginator($query, $request->query->get('page', 1), $request->query->get('limit', 18));

        if ('json' === $format) {
            $result = array(
                'results' => array(),
                'total'   => $paginator->getNbResults(),
            );

            /* @var $developer Developer */
            foreach ($paginator as $developer) {
                $result['results'][] = array(
                    'name'          => $developer->getName(),
                    'email'         => $developer->getEmail(),
                    'avatarUrl'     => $developer->getAvatarUrl(),
                    'fullName'      => $developer->getFullName(),
                    'company'       => $developer->getCompany(),
                    'location'      => $developer->getLocation(),
                    'blog'          => $developer->getUrl(),
                    'lastCommitAt'  => $developer->getLastCommitAt() ? $developer->getLastCommitAt()->getTimestamp() : null,
                    'score'         => $developer->getScore(),
                    'url'           => $this->generateUrl('developer_show', array('name' => $developer->getName()), true)
                );
            }

            if ($paginator->hasPreviousPage()) {
                $result['prev'] = $this->generateUrl('developer_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getPreviousPage(),
                    'limit'   => $request->query->get('limit'),
                    '_format' => 'json',
                ), true);
            }

            if ($paginator->hasNextPage()) {
                $result['next'] = $this->generateUrl('developer_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getNextPage(),
                    'limit'   => $request->query->get('limit'),
                    '_format' => 'json',
                ), true);
            }

            return new JsonResponse($result);
        }

        $this->highlightMenu('developers');

        return $this->render('KnpBundlesBundle:Developer:list.html.twig', array(
            'developers'  => $paginator,
            'sort'        => $sort,
            'sortLegends' => $this->sortLegends
        ));
    }

    public function bundlesAction($name)
    {
        /* @var $developer Developer */
        if (!$developer = $this->getRepository('Developer')->findOneByName($name)) {
            return new JsonResponse(array('status' => 'error', 'message' => 'Developer not found.'), 404);
        }

        $result = array(
            'developer' => $developer->getName(),
            'bundles'   => array(),
        );

        /* @var $bundle Bundle */
        foreach ($developer->getBundles() as $bundle) {
            $result['bundles'][] = array(
                'name'  => $bundle->getFullName(),
                'state' => $bundle->getState(),
                'score' => $bundle->getScore(),
                'url'   => $this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName()), true)
            );
        }

        return new JsonResponse($result);
    }
}
