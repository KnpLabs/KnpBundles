<?php

namespace Knp\Bundle\KnpBundlesBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Console\Output\NullOutput as Output;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Project;
use Knp\Bundle\KnpBundlesBundle\Entity\Link;
use Zend\Paginator\Paginator;
use Knp\Menu\MenuItem;
use Knp\Bundle\KnpBundlesBundle\Updater\Updater;
use Knp\Bundle\KnpBundlesBundle\Updater\Exception\UserNotFoundException;

class RepoController extends Controller
{
    protected $sortFields = array(
        'best'          => 'score',
        'updated'       => 'lastCommitAt',
        'newest'        => 'createdAt'
    );

    protected $sortLegends = array(
        'best'          => 'bundles.sort.best',
        'updated'       => 'bundles.sort.updated',
        'newest'        => 'bundles.sort.newest'
    );

    public function searchAction()
    {
        $query = preg_replace('(\W)', '', trim($this->get('request')->query->get('q')));

        if (empty($query)) {
            return $this->render('KnpBundlesBundle:Repo:search.html.twig');
        }

        $repos = $this->getRepository('Repo')->search($query);
        $bundles = $projects = array();
        foreach ($repos as $repo) {
            if ($repo instanceof Bundle) {
                $bundles[] = $repo;
            } else {
                $projects[] = $repo;
            }
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        return $this->render('KnpBundlesBundle:Repo:searchResults.'.$format.'.twig', array(
            'query'         => $query,
            'repos'         => $repos,
            'bundles'       => $bundles,
            'projects'      => $projects,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function showAction($username, $name)
    {
        $repo = $this->getRepository('Repo')->findOneByUsernameAndName($username, $name);
        if (!$repo) {
            throw new NotFoundHttpException(sprintf('The repo "%s/%s" does not exist', $username, $name));
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $this->highlightMenu($repo instanceof Bundle);

        return $this->render('KnpBundlesBundle:'.$repo->getClass().':show.'.$format.'.twig', array(
            'repo'          => $repo,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function listAction($sort, $class)
    {
        if (!array_key_exists($sort, $this->sortFields)) {
            throw new HttpException(406, sprintf('%s is not a valid sorting field', $sort));
        }

        $format = $this->get('request')->query->get('format', 'html');
        if (!in_array($format, array('html', 'json', 'js'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        $sortField = $this->sortFields[$sort];

        if ('html' === $format) {
            $query = $this->getRepository($class)->queryAllWithUsersAndContributorsSortedBy($sortField);
            $repos = $this->getPaginator($query, $this->get('request')->query->get('page', 1));
        } else {
            $repos = $this->getRepository($class)->findAllWithUsersAndContributorsSortedBy($sortField);
        }

        $this->highlightMenu('Bundle' == $class);

        return $this->render('KnpBundlesBundle:'.$class.':list.'.$format.'.twig', array(
            'repos'         => $repos,
            'sort'          => $sort,
            'sortLegends'   => $this->sortLegends,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function listLatestAction()
    {
        $repos = $this->getRepository('Repo')->findAllSortedBy('createdAt', 50);

        $format = $this->get('request')->query->get('format', 'atom');
        if (!in_array($format, array('atom'))) {
            throw new NotFoundHttpException(sprintf('The format "%s" does not exist', $format));
        }
        $this->get('request')->setRequestFormat($format);

        return $this->render('KnpBundlesBundle:Repo:listLatest.'.$format.'.twig', array(
            'repos'         => $repos,
            'callback'      => $this->get('request')->query->get('callback')
        ));
    }

    public function addLinkAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirect($this->generateUrl('bundle_list'));
        }
        
        $url = $request->request->get('url');
        $repoId = (int)$request->request->get('repo_id');
        $repo = $this->getRepository('Repo')->find($repoId);

        if ($repo && $repo->hasLink($url)) {
            $error = true;
            $errorMessage = 'links.errors.linkExists';
        } elseif (!preg_match('$(http|https|ftp)://([\w-]+\.)+[\w-]+(/[\w- ./?%&=]*)?$', $url)) {
            $error = true;
            $errorMessage = 'links.errors.enterValidUrl';
        } else {
            $link = new Link($url);
            $repo->addLink($link);
            
            $em = $this->get('doctrine')->getEntityManager();
            $em->persist($repo);
            $em->flush();
            
            $error = false;
            $errorMessage = '';
        } 

        $data = array('repo' => $repo, 'error' => $error, 'errorMessage' => $errorMessage, 'url' => $url);

        return $this->render('KnpBundlesBundle:Repo:links.html.twig', $data);
    }

    public function addAction(Request $request)
    {
        if ($request->request->has('repo') ) {
            $repo = $request->request->get('repo');

            if (preg_match('/^[A-Za-z0-9-\.]+\/[A-Za-z0-9-\.]+$/', $repo)) {
                $updater = $this->get('knp_bundles.updater');
                $updater->setUp();
                try {
                    $repos = $updater->addRepo($repo, false);
    
                    $repoParts = explode('/', $repo);
                    $params = array('username' => $repoParts[0], 'name' => $repoParts[1]);
    
                    return $this->redirect($this->generateUrl('repo_show', $params));
                } catch (UserNotFoundException $e) {
                    $error = true;
                    $errorMessage = 'addRepo.userNotFound';
                }
            } else {
                $error = true;
                $errorMessage = 'addRepo.invalidRepoName';
            }
        } else {
            $repo = '';
            $error = false;
            $errorMessage = '';
        }

        $data = array('repo' => $repo, 'error' => $error, 'errorMessage' => $errorMessage);
        
        return $this->render('KnpBundlesBundle:Repo:add.html.twig', $data);
    }

    /**
     * Returns the paginator instance configured for the given query and page
     * number
     *
     * @param  Query   $query The query
     * @param  integer $page  The current page number
     *
     * @return Paginator
     */
    protected function getPaginator(Query $query, $page)
    {
        $adapter = $this->get('knp_bundles.paginator')->getAdapter();
        $adapter->setQuery($query);

        $this->get('knp_bundles.paginator')->setCurrentPageNumber($page);

        return $this->get('knp_bundles.paginator');
    }

    protected function getUserRepository()
    {
        return $this->getRepository('User');
    }

    protected function getRepository($class)
    {
        return $this->get('knp_bundles.entity_manager')->getRepository('Knp\\Bundle\\KnpBundlesBundle\\Entity\\'.$class);
    }

    protected function highlightMenu($highlightBundlesMenu)
    {
        if ($highlightBundlesMenu) {
            $this->get('knp_bundles.menu.main')->getChild('bundles')->setCurrent(true);
        } else {
            $this->get('knp_bundles.menu.main')->getChild('projects')->setCurrent(true);
        }
    }
}