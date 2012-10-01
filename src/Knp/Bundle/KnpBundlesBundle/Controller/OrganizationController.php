<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\Entity\Organization;

class OrganizationController extends BaseController
{
    protected $sortFields = array(
        'name' => 'name',
        'bundles' => 'bundles',
        'developers' => 'developers'
    );

    protected $sortLegends = array(
        'name' => 'organizations.sort.name',
        'bundles' => 'organizations.sort.bundles',
        'developers' => 'organizations.sort.developers',
    );

    public function showAction(Request $request, $name)
    {
        $format = $request->getRequestFormat();
        /* @var $organization Organization */
        if (!$organization = $this->getRepository('Organization')->findOneByNameWithRepos($name)) {
            if ('json' === $format) {
                return new JsonResponse(array('status' => 'error', 'message' => 'Organization not found.'), 404);
            }

            throw new NotFoundHttpException(sprintf('The organization "%s" does not exist', $name));
        }

        if ('json' === $format) {
            return new JsonResponse($organization->toSmallArray());
        }

        $this->highlightMenu('organizations');

        return $this->render('KnpBundlesBundle:Organization:show.html.twig', array(
            'organization' => $organization
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

        $this->highlightMenu('organizations');

        $query = $this->getRepository('Organization')->queryAllWithBundlesSortedBy($sortField);
        $paginator = $this->getPaginator($query, $request->query->get('page', 1), 18);

        $organizations = $paginator->getCurrentPageResults();
        /**
         * @see http://stackoverflow.com/a/8527531
         */
        if (is_array($organizations[0])) {
            foreach ($organizations as $i => $org) {
                $organizations[$i] = $org[0];
            }
        }

        if ('json' === $format) {
            $result = array(
                'results' => array(),
                'total'   => $paginator->getNbResults(),
            );

            /* @var $organization Organization */
            foreach ($organizations as $organization) {
                $result['results'][] = $organization->toSmallArray() + array(
                    'url' => $this->generateUrl('organization_show', array('name' => $organization->getName()), true)
                );
            }

            if ($paginator->hasPreviousPage()) {
                $result['prev'] = $this->generateUrl('organization_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getPreviousPage(),
                    '_format' => 'json',
                ), true);
            }

            if ($paginator->hasNextPage()) {
                $result['next'] = $this->generateUrl('organization_list', array(
                    'sort'    => $sort,
                    'page'    => $paginator->getNextPage(),
                    '_format' => 'json',
                ), true);
            }

            return new JsonResponse($result);
        }

        return $this->render('KnpBundlesBundle:Organization:list.html.twig', array(
            'paginator'   => $paginator,
            'sortLegends' => $this->sortLegends,
            'sort'        => $sort
        ));
    }

    public function bundlesAction($name)
    {
        /* @var $organization Organization */
        if (!$organization = $this->getRepository('Organization')->findOneByName($name)) {
            return new JsonResponse(array('status' => 'error', 'message' => 'Organization not found.'), 404);
        }

        $result = array(
            'organization' => $organization->getName(),
            'bundles'      => array(),
        );

        /* @var $bundle Bundle */
        foreach ($organization->getBundles() as $bundle) {
            $result['bundles'][] = array(
                'name'  => $bundle->getFullName(),
                'state' => $bundle->getState(),
                'score' => $bundle->getScore(),
                'url'   => $this->generateUrl('bundle_show', array('ownerName' => $bundle->getOwnerName(), 'name' => $bundle->getName()), true)
            );
        }

        return new JsonResponse($result);
    }

    public function membersAction($name)
    {
        /* @var $organization Organization */
        if (!$organization = $this->getRepository('Organization')->findOneByName($name)) {
            return new JsonResponse(array('status' => 'error', 'message' => 'Organization not found.'), 404);
        }

        $result = array(
            'organization' => $organization->getName(),
            'members'      => array(),
        );

        /* @var $developer Developer */
        foreach ($organization->getMembers() as $developer) {
            $result['members'][] = array(
                'name'      => $developer->getName(),
                'full_name' => $developer->getFullName(),
                'company'   => $developer->getCompany(),
                'location'  => $developer->getLocation(),
                'blog'      => $developer->getUrl(),
                'score'     => $developer->getScore(),
                'url'       => $this->generateUrl('developer_show', array('name' => $developer->getName()), true)
            );
        }

        return new JsonResponse($result);
    }
}
