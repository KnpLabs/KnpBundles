<?php

namespace Knplabs\Bundle\Symfony2BundlesBundle\Tests\Git;

use Knplabs\Bundle\Symfony2BundlesBundle\Git\RepoManager;
use Knplabs\Bundle\Symfony2BundlesBundle\Git\Repo;
use Knplabs\Bundle\Symfony2BundlesBundle\Entity\Repo as RepoEntity;

class RepoManagerTest extends \PHPUnit_Framework_TestCase
{
    public function getDir()
    {
        $manager = $this->getManager();
        $this->assertTrue(is_dir($manager->getDir()));
    }

    public function testGetRepo()
    {
        $gitRepo = $this->getRepo();
        $this->assertTrue($gitRepo instanceof Repo);
    }

    protected function getRepo($repoFullName = 'FriendsOfSymfony/UserBundle')
    {
        $manager = $this->getManager();
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }

    protected function getManager()
    {
        $dir = sys_get_temp_dir().'/s2b_git_repos';
        $manager = new RepoManager($dir, $_SERVER['GIT_BIN']);

        return $manager;
    }
}
