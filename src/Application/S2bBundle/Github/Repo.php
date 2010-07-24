<?php

namespace Application\S2bBundle\Github;
use Symfony\Components\Console\Output\OutputInterface;
use Application\S2bBundle\Entities;

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
    
    public function __construct(\phpGitHubApi $github, OutputInterface $output)
    {
        $this->github = $github;
        $this->output = $output;
    }

    public function update(Entities\Repo $repo)
    {
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
     * @param Entities\Repo $repo 
     * @param array $data 
     * @return boolean whether the Repo exists on GitHub
     */
    public function updateInfos(Entities\Repo $repo)
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

    public function updateCommits(Entities\Repo $repo)
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
        $repo->setLastCommits(array_slice($commits, 0, 10));

        return $repo;
    }

    public function updateFiles(Entities\Repo $repo)
    {
        $this->output->write(' files');
        try {
            $blobs = $this->github->getObjectApi()->listBlobs($repo->getUsername(), $repo->getName(), 'master');
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }
        if($repo instanceof Entities\Project && !isset($blobs['src/autoload.php'])) {
            return false;
        }
        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if(isset($blobs[$readmeFilename])) {
                $readmeSha = $blobs[$readmeFilename];
                try {
                    $readmeText = $this->github->getObjectApi()->getRawData($repo->getUsername(), $repo->getName(), $readmeSha);
                    $repo->setReadme($readmeText);
                }
                catch(\phpGitHubApiRequestException $e) {
                    $this->output->write(sprintf('{%s}', $e->getCode()));
                }
                break;
            }
        }

        return $repo;
    }

    public function validateFiles(Entities\Repo $repo)
    {
        if($repo instanceof Entities\Bundle) {
            return true;
        }
        try {
            $blobs = $this->github->getObjectApi()->listBlobs($repo->getUsername(), $repo->getName(), 'master');
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }

        return isset($blobs['src/autoload.php']);
    }

    public function updateTags(Entities\Repo $repo)
    {
        $this->output->write(' tags');
        try {
            $tags = $this->github->getRepoApi()->getRepoTags($repo->getUsername(), $repo->getName());
        }
        catch(\phpGitHubApiRequestException $e) {
            if(404 == $e->getCode()) {
                return false;
            }
            throw $e;
        }
        $repo->setTags(array_keys($tags));

        return $repo;
    }

    public function getContributorNames(Entities\Repo $repo)
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
