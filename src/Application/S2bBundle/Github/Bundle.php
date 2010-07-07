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
        try {
            $this->updateInfos($bundle, $data);
        }
        catch(\phpGitHubApiRequestException $e) {
            $this->output->writeLn(sprintf('%s/%s is not a valid GitHub repo', $username, $name));
            $bundle = null;
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

    public function updateInfos(Document\Bundle $bundle, array $data = null)
    {
        if (null === $data) {
            $data = $this->github->getRepoApi()->show($bundle->getUsername(), $bundle->getName());
        }

        $bundle->setDescription($data['description']);
        $bundle->setFollowers(isset($data['followers']) ? $data['followers'] : $data['watchers']);
        $bundle->setForks($data['forks']);
        $bundle->setCreatedAt(new \DateTime(isset($data['created']) ? $data['created'] : $data['created_at']));
        $bundle->setIsOnGithub(true);
    }

    public function updateCommits(Document\Bundle $bundle)
    {
        $commits = $this->github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'master');
        if(empty($commits)) {
            return $this->forward('S2bBundle:Bundle:listAll', array('sort' => 'score'));
        }
        $bundle->setLastCommits(array_slice($commits, 0, 5));
    }

    public function updateFiles(Document\Bundle $bundle)
    {
        $blobs = $this->github->getObjectApi()->listBlobs($bundle->getUsername(), $bundle->getName(), 'master');
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
        $tags = $this->github->getRepoApi()->getRepoTags($bundle->getUsername(), $bundle->getName());
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
