<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        if (!$organization = $this->getRepository('Organization')->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The organization "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat($request);

        $this->highlightMenu('organizations');

        return $this->render('KnpBundlesBundle:Organization:show.'.$format.'.twig', array(
            'organization' => $organization,
            'callback'     => $request->query->get('callback')
        ));
    }

    public function listAction(Request $request, $sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->recognizeRequestFormat($request);

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

        return $this->render('KnpBundlesBundle:Organization:list.'.$format.'.twig', array(
            'organizations' => $organizations,
            'paginator'     => $paginator,
            'callback'      => $request->query->get('callback'),
            'sortLegends'   => $this->sortLegends,
            'sort'          => $sort
        ));
    }

    public function bundlesAction(Request $request, $name)
    {
        $format = $this->recognizeRequestFormat($request);

        if ($format == 'html') {
            return $this->redirect($this->generateUrl('organization_show', array('name' => $name)));
        }

        if (!$organization = $this->getRepository('Organization')->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The organization "%s" does not exist', $name));
        }

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'  => $organization->getBundles(),
            'callback' => $request->query->get('callback')
        ));
    }

    public function membersAction(Request $request, $name)
    {
        $format = $this->recognizeRequestFormat($request);

        if ($format == 'html') {
            return $this->redirect($this->generateUrl('organization_show', array('name' => $name)));
        }

        if (!$organization = $this->getRepository('Organization')->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The organization "%s" does not exist', $name));
        }

        return $this->render('KnpBundlesBundle:Developer:list.'.$format.'.twig', array(
            'bundles'  => $organization->getMembers(),
            'callback' => $request->query->get('callback')
        ));
    }
}
