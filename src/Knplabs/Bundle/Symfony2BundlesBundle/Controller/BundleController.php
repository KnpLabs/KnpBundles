<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;

class BundleController
{
    public function __construct(EngineInterface $templating, EntityManager $em)
    {
        $this->templating = $templating;
        $this->em = $em;
    }

    public function listNewestAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('createdAt', 5);

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Bundle:listNewest.html.twig', array('bundles' => $bundles));
    }

    public function listBestScoreAction()
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('score', 5);

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Bundle:listBestScore.html.twig', array('bundles' => $bundles));
    }

    public function listFeaturedAction($max = 3)
    {
        $bundles = $this->getBundleRepository()->findAllSortedBy('nbFollowers', $max);

        return $this->templating->renderResponse('KnplabsSymfony2BundlesBundle:Bundle:listFeatured.html.twig', array('bundles' => $bundles));
    }

    /**
     * Returns the bundle repository
     *
     * @return  Knplabs\Symfony2Bundles\Entity\BundleRepository
     */
    protected function getBundleRepository()
    {
        return $this->em->getRepository('Knplabs\Bundle\Symfony2BundlesBundle\Entity\Bundle');
    }
}
