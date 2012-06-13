<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Github\Client;
use Github\HttpClient\Exception as GithubException;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Detector;
use Knp\Bundle\KnpBundlesBundle\Event\BundleEvent;

class Repo
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \Github\Client|null
     */
    protected $github = null;

    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Git\RepoManager|null
     */
    protected $gitRepoManager = null;

    /**
     * Output buffer
     *
     * @var null|\Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param \Github\Client $github
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Knp\Bundle\KnpBundlesBundle\Git\RepoManager $gitRepoManager
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(Client $github, OutputInterface $output, Git\RepoManager $gitRepoManager, EventDispatcherInterface $dispatcher)
    {
        $this->github = $github;
        $this->output = $output;
        $this->gitRepoManager = $gitRepoManager;
        $this->dispatcher = $dispatcher;
    }

    public function update(Bundle $bundle)
    {
        try {
            $this->gitRepoManager->getRepo($bundle)->update();
        } catch (\GitRuntimeException $e) {
            return false;
        }

        if (!$this->updateInfos($bundle)) {
            return false;
        }
        if (!$this->updateFiles($bundle)) {
            return false;
        }
        if (!$this->updateCommits($bundle)) {
            return false;
        }
        if (!$this->updateTags($bundle)) {
            return false;
        }

        $event = new BundleEvent($bundle);
        $this->dispatcher->dispatch(BundleEvent::UPDATE_SCORE, $event);
        $bundle->recalculateScore();

        return $bundle;
    }

    /**
     * Return true if the Repo exists on GitHub, false otherwise
     *
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @param array $data
     * @return boolean whether the Repo exists on GitHub
     */
    public function updateInfos(Bundle $bundle)
    {
        $this->output->write(' infos');
        try {
            $data = $this->github->getRepoApi()->show($bundle->getUsername(), $bundle->getName());
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return false;
            }
            throw $e;
        }

        // Let's try to only keep a forked repo with lots of watchers
        if ($data['fork'] && $data['watchers'] < 10) {
            return false;
        }

        $bundle->setDescription(empty($data['description']) ? null : $data['description']);
        $bundle->setNbFollowers($data['watchers']);
        $bundle->setNbForks($data['forks']);
        $bundle->setCreatedAt(new \DateTime($data['created_at']));
        $bundle->setHomepage(empty($data['homepage']) ? null : $data['homepage']);

        return $bundle;
    }

    public function updateCommits(Bundle $bundle)
    {
        $this->output->write(' commits');
        try {
            $commits = $this->github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'HEAD');
            foreach ($commits as $key => $commit) {
                $commitDetailedInfo = $this->github->getCommitApi()->getCommit($bundle->getUsername(), $bundle->getName(), $commit['sha']);
                $commits[$key]['committer'] = $commitDetailedInfo['committer'];
            }
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return false;
            }
            throw $e;
        }
        if (empty($commits)) {
            return false;
        }
        $bundle->setLastCommits(array_slice($commits, 0, 30));

        return $bundle;
    }

    public function updateCommitsFromGitRepo(Bundle $bundle)
    {
        $this->output->write(' commits');
        $commits = $this->gitRepoManager->getRepo($bundle)->getCommits(30);
        $bundle->setLastCommits($commits);

        return $bundle;
    }

    public function updateFiles(Bundle $bundle)
    {
        $this->output->write(' files');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        foreach (array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if ($gitRepo->hasFile($readmeFilename)) {
               $bundle->setReadme($gitRepo->getFileContent($readmeFilename));
               break;
            }
        }

        foreach (array('LICENSE', 'Resources\meta\LICENSE') as $licenseFilename) {
            if ($gitRepo->hasFile($licenseFilename)) {
                $bundle->setLicense($gitRepo->getFileContent($licenseFilename));
                break;
            }
        }

        $bundle->setUsesTravisCi($gitRepo->hasFile('.travis.yml'));

        $this->updateComposerFile($gitRepo, $bundle);

        return $bundle;
    }

    private function updateComposerFile($gitRepo, Bundle $bundle)
    {
        $composerFilename = 'composer.json';

        $composerName = null;
        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename), true);

            $composerName = isset($composer['name']) ? $composer['name'] : null;

            // looking for required version of Symfony
            if (isset($composer['require'])) {
                foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {
                    if (isset($composer['require'][$requirement])) {
                        $bundle->setSymfonyVersion($composer['require'][$requirement]);
                        break;
                    }
                }
            }
        }

        $bundle->setComposerName($composerName);
    }

    public function updateTags(Bundle $bundle)
    {
        $this->output->write(' tags');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);
        $tags = $gitRepo->getGitRepo()->getTags();
        $bundle->setTags($tags);

        return $bundle;
    }

    public function fetchComposerKeywords(Bundle $bundle)
    {
        $composerFilename = 'composer.json';
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename));

            return isset($composer->keywords) ? $composer->keywords : array();
        }

        return array();
    }

    public function getContributorNames(Bundle $bundle)
    {
        try {
            $contributors = $this->github->getRepoApi()->getRepoContributors($bundle->getUsername(), $bundle->getName());
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return array();
            }
            throw $e;
        }
        $names = array();
        foreach ($contributors as $contributor) {
            if ($bundle->getUsername() != $contributor['login']) {
                $names[] = $contributor['login'];
            }
        }

        return $names;
    }

    /**
     * Checks if '*Bundle.php' class use base class for Symfony bundle
     *
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     *
     * @return bool
     */
    public function isValidSymfonyBundle(Bundle $bundle)
    {
        if (null === $bundleClassContent = $this->getBundleClassContentAsString($bundle)) {
            return false;
        }

        return false !== strpos($bundleClassContent, 'Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    /**
     * Get output
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set output
     *
     * @param $output OutputInterface
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return \Github\Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param \Github\Client
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }

    /**
     * Returns content for *Bundle.php class via Github API v3
     *
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     *
     * @return null|string
     */
    private function getBundleClassContentAsString(Bundle $bundle)
    {
        $rootContents = $this->github->getRepoApi()->getRepoContents($bundle->getUsername(), $bundle->getName(), '');
        foreach ($rootContents as $rootEntry) {
            if (strpos($rootEntry['name'], 'Bundle.php') !== false) {
                $response = json_decode(file_get_contents($rootEntry['_links']['git']));

                return base64_decode($response->content);
            }
        }

        return null;
    }
}
