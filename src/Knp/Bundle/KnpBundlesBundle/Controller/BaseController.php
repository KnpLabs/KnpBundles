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
     * Recognizes request format based on 'format' parameter in request.
     * Returns recognized format.
     *
     * @param  Request $request
     * @param  array   $supported      Array of supported formats.
     * @param  string  $default        Default format.
     *
     * @return string
     *
     * @throws NotFoundHttpException
     */
    protected function recognizeRequestFormat(Request $request, $supported = array('html', 'json', 'js'), $default = 'html')
    {
        $format = $request->query->get('format', $default);

        if (!in_array($format, $supported)) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }

        $request->setRequestFormat($format);

        return $format;
    }

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

    protected function getBundleRepository()
    {
        return $this->getRepository('Bundle');
    }

    protected function getUserRepository()
    {
        return $this->getRepository('User');
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
