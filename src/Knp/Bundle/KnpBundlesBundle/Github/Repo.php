<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\KnpBundlesBundle\Entity;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Detector;

class Repo
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \Github_Client
     */
    protected $github = null;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    protected $output = null;

    public function __construct(\Github_Client $github, OutputInterface $output, Git\RepoManager $gitRepoManager)
    {
        $this->github = $github;
        $this->output = $output;
        $this->gitRepoManager = $gitRepoManager;
    }

    public function update(Entity\Bundle $bundle)
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
        $bundle->recalculateScore();

        return $bundle;
    }

    /**
     * Return true if the Repo exists on GitHub, false otherwise
     *
     * @param Entity\Bundle $bundle
     * @param array $data
     * @return boolean whether the Repo exists on GitHub
     */
    public function updateInfos(Entity\Bundle $bundle)
    {
        $this->output->write(' infos');
        try {
            $data = $this->github->getRepoApi()->show($bundle->getUsername(), $bundle->getName());
        } catch (\Github_HttpClient_Exception $e) {
            if (404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }

        if($data['fork']) {
            if ($data['watchers'] >= 10) {
                // Let's try to keep a forked repo with lots of watchers
            } else {
                return false;
            }
        }

        $bundle->setDescription(empty($data['description']) ? null : $data['description']);
        $bundle->setNbFollowers($data['watchers']);
        $bundle->setNbForks($data['forks']);
        $bundle->setCreatedAt(new \DateTime($data['created_at']));
        $bundle->setHomepage(empty($data['homepage']) ? null : $data['homepage']);

        return $bundle;
    }

    public function updateCommits(Entity\Bundle $bundle)
    {
        $this->output->write(' commits');
        try {
            $commits = $this->github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'HEAD');
        } catch (\Github_HttpClient_Exception $e) {
            if (404 == $e->getCode()) {
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

    public function updateCommitsFromGitRepo(Entity\Bundle $bundle)
    {
        $this->output->write(' commits');
        $commits = $this->gitRepoManager->getRepo($bundle)->getCommits(30);
        $bundle->setLastCommits($commits);

        return $bundle;
    }

    public function updateFiles(Entity\Bundle $bundle)
    {
        $this->output->write(' files');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if ($gitRepo->hasFile($readmeFilename)) {
               $bundle->setReadme($gitRepo->getFileContent($readmeFilename));
            }
        }

        $bundle->setUsesTravisCi($gitRepo->hasFile('.travis.yml'));

        $this->updateComposerFile($gitRepo, $bundle);

        return $bundle;
    }

    private function updateComposerFile($gitRepo, Entity\Bundle $bundle)
    {
        $composerFilename = 'composer.json';

        $composerName = null;
        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename));

            $composerName = isset($composer->name) ? $composer->name : null;
        }

        $bundle->setComposerName($composerName);
    }
    
    public function updateTags(Entity\Bundle $bundle)
    {
        $this->output->write(' tags');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);
        $tags = $gitRepo->getGitRepo()->getTags();
        $bundle->setTags($tags);

        return $bundle;
    }

    public function fetchComposerKeywords(Entity\Bundle $bundle)
    {
        $composerFilename = 'composer.json';
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename));

            return isset($composer->keywords) ? $composer->keywords : array();
        }
    }

    public function getContributorNames(Entity\Bundle $bundle)
    {
        try {
            $contributors = $this->github->getRepoApi()->getRepoContributors($bundle->getUsername(), $bundle->getName());
        } catch (\Github_HttpClient_Exception $e) {
            if (404 == $e->getCode()) {
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
     * @param  OutputInterface
     * @return null
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return \Github_Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param  \Github_Client
     * @return null
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }
}
