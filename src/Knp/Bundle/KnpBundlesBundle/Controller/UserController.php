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
        'name'          => 'name',
        'best'          => 'score',
    );

    protected $sortLegends = array(
        'name'          => 'users.sort.name',
        'best'          => 'users.sort.best',
    );

    public function showAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat();

        $this->highlightMenu();

        return $this->render('KnpBundlesBundle:User:show.'.$format.'.twig', array(
            'user'      => $user,
            'callback'  => $this->get('request')->query->get('callback')
        ));
    }

    public function listAction($sort = 'name')
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $format = $this->recognizeRequestFormat();

        $sortField = $this->sortFields[$sort];

        $this->highlightMenu();

        if ('html' === $format) {
            $query = $this->getUserRepository()->queryAllWithBundlesSortedBy($sortField);
            $users = $this->getPaginator($query, $this->get('request')->query->get('page', 1));
        } else {
            $users = $this->getUserRepository()->findAllWithBundlesSortedBy($sortField);
        }

        return $this->render('KnpBundlesBundle:User:list.'.$format.'.twig', array(
            'users'         => $users,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function bundlesAction($name)
    {
        if (!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->recognizeRequestFormat();

        return $this->render('KnpBundlesBundle:Bundle:list.'.$format.'.twig', array(
            'bundles'   => $user->getBundles(),
            'callback'  => $this->get('request')->query->get('callback')
        ));
    }

    protected function highlightMenu()
    {
        $this->get('knp_bundles.menu.main')->getChild('users')->setCurrent(true);
    }
}
