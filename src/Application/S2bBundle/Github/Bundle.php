<?php

namespace Application\S2bBundle\Github;
use Symfony\Components\Console\Output\OutputInterface;
use Application\S2bBundle\Document;

class Bundle
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

    public function import($username, $name, array $data = null)
    {
        $bundle = new Document\Bundle();
        $bundle->setName($name);
        $bundle->setUsername($username);
        if(!$this->updateInfos($bundle)) {
            return false;
        }
        return $bundle;
    }

    public function update(Document\Bundle $bundle)
    {
        $this->updateCommits($bundle);
        $this->updateFiles($bundle);
        $this->updateTags($bundle);
        $bundle->recalculateScore();
    }

    /**
     * Return true if the Bundle exists on GitHub, false otherwise 
     * 
     * @param Document\Bundle $bundle 
     * @param array $data 
     * @return boolean whether the Bundle exists on GitHub
     */
    public function updateInfos(Document\Bundle $bundle, array $data = null)
    {
        if (null === $data) {
            try {
                $data = $this->github->getRepoApi()->show($bundle->getUsername(), $bundle->getName());
            }
            catch(\phpGitHubApiRequestException $e) {
                if(404 == $e->getCode()) {
                    return false;
                }
                sleep(2);
                return $this->updateInfos($bundle);
            }
        }

        $bundle->setDescription($data['description']);
        $bundle->setFollowers(isset($data['followers']) ? $data['followers'] : $data['watchers']);
        $bundle->setForks($data['forks']);
        $bundle->setIsFork((bool)$data['fork']);
        $bundle->setCreatedAt(new \DateTime(isset($data['created']) ? $data['created'] : $data['created_at']));
        $bundle->setIsOnGithub(true);

        return true;
    }

    public function updateCommits(Document\Bundle $bundle)
    {
        try {
            $commits = $this->github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'master');
        }
        catch(\phpGitHubApiRequestException $e) {
            sleep(2);
            return $this->updateCommits($bundle);
        }
        if(empty($commits)) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $bundle->setLastCommits(array_slice($commits, 0, 5));
    }

    public function updateFiles(Document\Bundle $bundle)
    {
        try {
            $blobs = $this->github->getObjectApi()->listBlobs($bundle->getUsername(), $bundle->getName(), 'master');
        }
        catch(\phpGitHubApiRequestException $e) {
            sleep(2);
            return $this->updateFiles($bundle);
        }
        foreach(array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if(isset($blobs[$readmeFilename])) {
                $readmeSha = $blobs[$readmeFilename];
                $readmeText = $this->github->getObjectApi()->getRawData($bundle->getUsername(), $bundle->getName(), $readmeSha);
                $bundle->setReadme($readmeText);
                break;
            }
        }
    }

    public function updateTags(Document\Bundle $bundle)
    {
        try {
            $tags = $this->github->getRepoApi()->getRepoTags($bundle->getUsername(), $bundle->getName());
        }
        catch(\phpGitHubApiRequestException $e) {
            sleep(2);
            return $this->updateTags($bundle);
        }
        $bundle->setTags(array_keys($tags));
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
