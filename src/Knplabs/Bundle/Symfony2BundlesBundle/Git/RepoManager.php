<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Git;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo as RepoEntity;
use Symfony\Component\HttpKernel\Util\Filesystem;

class RepoManager
{
    /**
     * Repositories base directory
     *
     * @var string
     */
    protected $dir = null;

    protected $filesystem = null;

    public function __construct($dir)
    {
        $this->dir = $dir;
        $this->filesystem = new Filesystem();

        $this->filesystem->mkdir($this->dir);
    }

    public function getRepo(RepoEntity $repo)
    {
        if($this->hasRepo($repo)) {
            $dir = $this->getRepoDir($repo);
            $gitRepo = new \phpGitRepo($dir);
        }
        else {
            $gitRepo = $this->createGitRepo($repo);
        }

        return new Repo($repo, $gitRepo);
    }

    public function hasRepo(RepoEntity $repo)
    {
        $dir = $this->getRepoDir($repo);
        return is_dir($dir.'/.git');
    }

    public function createGitRepo(RepoEntity $repo)
    {
        $dir = $this->getRepoDir($repo);
        $this->filesystem->mkdir($dir);
        $gitRepo = \phpGitRepo::create($dir);
        $gitRepo->git('remote add origin '.$repo->getGitUrl());
        $gitRepo->git('pull origin master');

        return $gitRepo;
    }

    public function getRepoDir(RepoEntity $repo)
    {
        return $this->dir.'/'.$repo->getUsername().'/'.$repo->getName();
    }

    /**
     * Get dir
     * @return string
     */
    public function getDir()
    {
      return $this->dir;
    }

    /**
     * Set dir
     * @param  string
     * @return null
     */
    public function setDir($dir)
    {
      $this->dir = $dir;
    }
}
