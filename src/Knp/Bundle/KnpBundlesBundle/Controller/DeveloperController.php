<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knp\Menu\MenuItem;

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
        if (!$developer = $this->getRepository('Developer')->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The developer "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat($request);

        $this->highlightMenu('developers');

        return $this->render('KnpBundlesBundle:Developer:show.'.$format.'.twig', array(
            'developer' => $developer,
            'callback'  => $request->query->get('callback')
        ));
    }

    public function listAction(Request $request, $sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->recognizeRequestFormat($request);

        $sortField = $this->sortFields[$sort];

        $this->highlightMenu('developers');

        $query = $this->getRepository('Developer')->queryAllWithBundlesSortedBy($sortField);
        $developers = $this->getPaginator($query, $request->query->get('page', 1), 18);

        return $this->render('KnpBundlesBundle:Developer:list.'.$format.'.twig', array(
            'developers'       => $developers,
            'sort'        => $sort,
            'sortLegends' => $this->sortLegends,
            'callback'    => $request->query->get('callback')
        ));
    }

    public function bundlesAction(Request $request, $name)
    {
        $format = $this->recognizeRequestFormat($request);

        if ($format == 'html') {
            return $this->redirect($this->generateUrl('developer_show', array('name' => $name)));
        }

        if (!$developer = $this->getRepository('Developer')->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The developer "%s" does not exist', $name));
        }

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'  => $developer->getBundles(),
            'callback' => $request->query->get('callback')
        ));
    }
}
