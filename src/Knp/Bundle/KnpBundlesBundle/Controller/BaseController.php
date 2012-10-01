<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;

class BaseController extends Controller
{
    /**
     * Returns the paginator instance configured for the given query and page
     * number
     *
     * @param  Query   $query The query
     * @param  integer $page  The current page number
     * @param  integer $limit Results per page
     *
     * @return Pagerfanta
     */
    protected function getPaginator(Query $query, $page, $limit = 10)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page, false, true)
        ;

        return $paginator;
    }

    protected function getRepository($class)
    {
        return $this->container->get('knp_bundles.entity_manager')->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\'.$class);
    }

    protected function highlightMenu($menu)
    {
        $this->get('knp_bundles.menu.main')->getChild($menu)->setCurrent(true);
    }
}
