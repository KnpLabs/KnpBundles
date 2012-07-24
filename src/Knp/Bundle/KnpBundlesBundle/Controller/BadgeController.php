<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller for returning bundle badges
 */
class BadgeController extends BaseController
{
    public function getBadgeAction($username, $name, $type = 'long')
    {
        $file = sprintf('%s/badges/%s/%s-%s.png', $this->container->getParameter('kernel.cache_dir'), $type, $username, $name);
        if (!file_exists($file)) {
            throw new NotFoundHttpException(sprintf('The badge is missing for "%s/%s"', $username, $name));
        }

        $relativePath = $this->findShortestPath(
            $this->container->getParameter('kernel.root_dir'),
            $this->container->getParameter('kernel.cache_dir')
        );

        return $this->get('igorw_file_serve.response_factory')->create(
            sprintf('%s/badges/%s/%s-%s.png', $relativePath, $type, $username, $name),
            'image/png'
        );
    }

    private function findShortestPath($from, $to)
    {
        if (!$this->isAbsolutePath($from) || !$this->isAbsolutePath($to)) {
            throw new \InvalidArgumentException('from and to must be absolute paths');
        }

        if (dirname($from) === dirname($to)) {
            return './'.basename($to);
        }
        $from = lcfirst(rtrim(strtr($from, '\\', '/'), '/'));
        $to = lcfirst(rtrim(strtr($to, '\\', '/'), '/'));

        $commonPath = $to;
        while (strpos($from, $commonPath) !== 0 && '/' !== $commonPath && !preg_match('{^[a-z]:/?$}i', $commonPath) && '.' !== $commonPath) {
            $commonPath = strtr(dirname($commonPath), '\\', '/');
        }

        if (0 !== strpos($from, $commonPath) || '/' === $commonPath || '.' === $commonPath) {
            return $to;
        }

        $commonPath = rtrim($commonPath, '/') . '/';
        $sourcePathDepth = substr_count(substr($from, strlen($commonPath)), '/');
        $commonPathCode = str_repeat('../', $sourcePathDepth);

        return ($commonPathCode . substr($to, strlen($commonPath))) ?: './';
    }

    /**
     * Checks if the given path is absolute
     *
     * @param string $path
     * @return Boolean
     */
    private function isAbsolutePath($path)
    {
        return substr($path, 0, 1) === '/' || substr($path, 1, 1) === ':';
    }
}
