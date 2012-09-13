<?php

namespace Knp\Bundle\KnpBundlesBundle\Git;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle as BundleEntity;
use Symfony\Component\Filesystem\Filesystem;
use PHPGit_Repository;

class RepoManager
{
    /**
     * Repositories base directory
     *
     * @var string
     */
    protected $dir = null;

    /**
     * @var Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem = null;

    /**
     * @var string
     */
    protected $gitExecutable;

    public function __construct(Filesystem $filesystem, $dir, $gitExecutable)
    {
        $this->filesystem = $filesystem;
        $this->dir = $dir;
        $this->gitExecutable = $gitExecutable;

        $this->filesystem->mkdir($this->dir);
    }

    /**
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @return \Knp\Bundle\KnpBundlesBundle\Git\Repo
     */
    public function getRepo(BundleEntity $bundle)
    {
        if($this->hasRepo($bundle)) {
            $dir = $this->getRepoDir($bundle);
            $repo = new PHPGit_Repository($dir, false, array('git_executable' => $this->gitExecutable));
        } else {
            $repo = $this->createGitRepo($bundle);
        }

        return new Repo($bundle, $repo);
    }

    /**
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @return boolean
     */
    public function hasRepo(BundleEntity $repo)
    {
        $dir = $this->getRepoDir($repo);

        return is_dir($dir.'/.git');
    }

    /**
     * Get dir
     *
     * @return string
     */
    public function getDir()
    {
      return $this->dir;
    }

    /**
     * Set dir
     *
     * @param  string
     * @return null
     */
    public function setDir($dir)
    {
      $this->dir = $dir;
    }

    /**
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @return PHPGit_Repository
     */
    protected function createGitRepo(BundleEntity $bundle)
    {
        $targetDir = $this->getRepoDir($bundle);
        $this->filesystem->mkdir($targetDir);

        return $this->cloneRepo($bundle->getGitUrl(), $targetDir);
    }

    /**
     * @param string $repoUrl
     * @param string $targetDir
     * @return PHPGit_Repository
     */
    protected function cloneRepo($repoUrl, $targetDir)
    {
        return PHPGit_Repository::cloneUrl($repoUrl, $targetDir, false, array('git_executable' => $this->gitExecutable));
    }

    /**
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @return string
     */
    protected function getRepoDir(BundleEntity $repo)
    {
        return $this->dir.DIRECTORY_SEPARATOR.$repo->getOwnerName().DIRECTORY_SEPARATOR.$repo->getName();
    }
}
