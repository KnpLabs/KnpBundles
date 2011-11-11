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

    public function update(Entity\Repo $repo)
    {
        try {
            $this->gitRepoManager->getRepo($repo)->update();
        } catch (\GitRuntimeException $e) {
            return false;
        }

        if (!$this->updateInfos($repo)) {
            return false;
        }
        if (!$this->updateFiles($repo)) {
            return false;
        }
        if (!$this->updateCommits($repo)) {
            return false;
        }
        if (!$this->updateTags($repo)) {
            return false;
        }
        $repo->recalculateScore();

        return $repo;
    }

    /**
     * Return true if the Repo exists on GitHub, false otherwise
     *
     * @param Entity\Repo $repo
     * @param array $data
     * @return boolean whether the Repo exists on GitHub
     */
    public function updateInfos(Entity\Repo $repo)
    {
        $this->output->write(' infos');
        try {
            $data = $this->github->getRepoApi()->show($repo->getUsername(), $repo->getName());
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

        $repo->setDescription(empty($data['description']) ? null : $data['description']);
        $repo->setNbFollowers($data['watchers']);
        $repo->setNbForks($data['forks']);
        $repo->setCreatedAt(new \DateTime($data['created_at']));
        $repo->setHomepage(empty($data['homepage']) ? null : $data['homepage']);

        return $repo;
    }

    public function updateCommits(Entity\Repo $repo)
    {
        $this->output->write(' commits');
        try {
            $commits = $this->github->getCommitApi()->getBranchCommits($repo->getUsername(), $repo->getName(), 'HEAD');
        } catch (\Github_HttpClient_Exception $e) {
            if (404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }
        if (empty($commits)) {
            return false;
        }
        $repo->setLastCommits(array_slice($commits, 0, 30));

        return $repo;
    }

    public function updateCommitsFromGitRepo(Entity\Repo $repo)
    {
        $this->output->write(' commits');
        $commits = $this->gitRepoManager->getRepo($repo)->getCommits(30);
        $repo->setLastCommits($commits);

        return $repo;
    }

    public function updateFiles(Entity\Repo $repo)
    {
        $this->output->write(' files');
        $gitRepo = $this->gitRepoManager->getRepo($repo);
        if ($repo instanceof Entity\Project) {
            $detector = new Detector\Project();
            if (!$detector->matches($gitRepo)) {
                return false;
            }
        }

        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if ($gitRepo->hasFile($readmeFilename)) {
               $repo->setReadme($gitRepo->getFileContent($readmeFilename));
            }
        }

        return $repo;
    }

    public function updateTags(Entity\Repo $repo)
    {
        $this->output->write(' tags');
        $gitRepo = $this->gitRepoManager->getRepo($repo);
        $tags = $gitRepo->getGitRepo()->getTags();
        $repo->setTags($tags);

        return $repo;
    }

    public function getContributorNames(Entity\Repo $repo)
    {
        try {
            $contributors = $this->github->getRepoApi()->getRepoContributors($repo->getUsername(), $repo->getName());
        } catch (\Github_HttpClient_Exception $e) {
            if (404 == $e->getCode()) {
                return array();
            }
            throw $e;
        }
        $names = array();
        foreach ($contributors as $contributor) {
            if ($repo->getUsername() != $contributor['login']) {
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
