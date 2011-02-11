<?php

namespace Knplabs\Symfony2BundlesBundle\Github;
use Symfony\Component\Console\Output\OutputInterface;
use Knplabs\Symfony2BundlesBundle\Entity;
use Knplabs\Symfony2BundlesBundle\Git;

class Repo
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \phpGitHubApi
     */
    protected $github = null;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    protected $output = null;

    public function __construct(\phpGitHubApi $github, OutputInterface $output, Git\RepoManager $gitRepoManager)
    {
        $this->github = $github;
        $this->output = $output;
        $this->gitRepoManager = $gitRepoManager;
    }

    public function update(Entity\Repo $repo)
    {
        try {
            $this->gitRepoManager->getRepo($repo)->update();
        }
        catch(\GitRuntimeException $e) {
            return false;
        }

        if(!$this->updateInfos($repo)) {
            return false;
        }
        if(!$this->updateFiles($repo)) {
            return false;
        }
        if(!$this->updateCommits($repo)) {
            return false;
        }
        if(!$this->updateTags($repo)) {
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
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }

        if($data['fork']) {
            return false;
        }

        $repo->setDescription($data['description']);
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
            $commits = $this->github->getCommitApi()->getBranchCommits($repo->getUsername(), $repo->getName(), 'master');
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }
        if(empty($commits)) {
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
        if($repo instanceof Entity\Project) {
            $detector = new Symfony2Detector($gitRepo->getDir());
            if(!$detector->isProject()) {
                return false;
            }
        }

        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if($gitRepo->hasFile($readmeFilename)) {
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
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return array();
            }
            throw $e;
        }
        $names = array();
        foreach($contributors as $contributor) {
            if($repo->getUsername() != $contributor['login']) {
                $names[] = $contributor['login'];
            }
        }

        return $names;
    }

    /**
     * Get output
     * @return OutputInterface
     */
    public function getOutput()
    {
      return $this->output;
    }

    /**
     * Set output
     * @param  OutputInterface
     * @return null
     */
    public function setOutput($output)
    {
      $this->output = $output;
    }

    /**
     * Get github
     * @return \phpGitHubApi
     */
    public function getGitHubApi()
    {
        return $this->github;
    }

    /**
     * Set github
     * @param  \phpGitHubApi
     * @return null
     */
    public function setGitHubApi($github)
    {
        $this->github = $github;
    }

}
