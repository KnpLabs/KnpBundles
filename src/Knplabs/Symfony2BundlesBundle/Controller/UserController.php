<?php

namespace Knplabs\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;

class UserController extends Controller
{
    protected $request;
    protected $templating;
    protected $em;

    protected $sortFields = array(
        'name'  => 'name',
        'score' => 'score'
    );

    public function __construct(Request $request, EngineInterface $templating, EntityManager $em)
    {
        $this->request = $request;
        $this->templating = $templating;
        $this->em = $em;
    }

    public function showAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByNameWithRepos($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:User:show.' . $format . '.twig', array(
            'user'      => $user,
            'callback'  => $this->request->get('callback')
        ));
    }

    public function listAction($sort = 'name')
    {
        if(!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(sprintf('%s is not a valid sorting field', $sort), 406);
        }

        $users = $this->getUserRepository()->findAllWithProjectsSortedBy($sort);

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:User:list.' . $format . '.twig', array(
            'users'         => $users,
            'sort'          => $sort,
            'sortFields'    => $this->sortFields,
            'callback'      => $this->request->get('callback')
        ));
    }

    public function bundlesAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Bundle:list.' . $format . '.twig', array(
            'repos'     => $user->getBundles(),
            'callback'  => $this->request->get('callback')
        ));
    }

    public function projectsAction($name)
    {
        if(!$user = $this->getUserRepository()->findOneByName($name)) {
            throw new NotFoundHttpException(sprintf('The user "%s" does not exist', $name));
        }

        $format = $this->request->get('_format');

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Project:list.' . $format . '.twig', array(
            'repos'     => $user->getProjects(),
            'callback'  => $this->request->get('callback')
        ));
    }

    protected function getBundleRepository()
    {
        return $this->em->getRepository('Knplabs\Symfony2BundlesBundle\Entity\Bundle');
    }

    protected function getUserRepository()
    {
        return $this->em->getRepository('Knplabs\Symfony2BundlesBundle\Entity\User');
    }
}
