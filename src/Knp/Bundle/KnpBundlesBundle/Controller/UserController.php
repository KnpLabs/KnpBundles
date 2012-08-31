<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Zend\Paginator\Paginator;
use Knp\Menu\MenuItem;

class UserController extends BaseController
{
    protected $sortFields = array(
        'name' => 'name',
        'best' => 'score',
    );

    protected $sortLegends = array(
        'name' => 'users.sort.name',
        'best' => 'users.sort.best',
    );

    public function userbarAction()
    {
        $response = $this->render('KnpBundlesBundle:User:userbar.html.twig');

        // this is private cache (don't cache with shared proxy)
        $response->setPrivate();
        // always revalidate cache
        $response->setMaxAge(0);
        $response->headers->addCacheControlDirective('must-revalidate', true);        
        
        return $response;
    }

    public function showAction(Request $request, $name)
    {
        if (!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat($request);

        $this->highlightMenu('users');

        return $this->render('KnpBundlesBundle:User:show.'.$format.'.twig', array(
            'user'     => $user,
            'callback' => $request->query->get('callback')
        ));
    }

    public function listAction(Request $request, $sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->recognizeRequestFormat($request);

        $sortField = $this->sortFields[$sort];

        $this->highlightMenu('users');

        $query = $this->getUserRepository()->queryAllWithBundlesSortedBy($sortField);
        $users = $this->getPaginator($query, $request->query->get('page', 1));

        return $this->render('KnpBundlesBundle:User:list.'.$format.'.twig', array(
            'users'       => $users,
            'sort'        => $sort,
            'sortLegends' => $this->sortLegends,
            'callback'    => $request->query->get('callback')
        ));
    }

    public function bundlesAction(Request $request, $name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat($request);

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'  => $user->getBundles(),
            'callback' => $request->query->get('callback')
        ));
    }
}
