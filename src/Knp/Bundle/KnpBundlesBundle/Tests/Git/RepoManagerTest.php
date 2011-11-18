<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Git\Repo;
use Knp\Bundle\KnpBundlesBundle\Entity\Repo as RepoEntity;

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

    protected function getRepo($repoFullName = 'knplabs/KnpGaufretteBundle')
    {
        $manager = $this->getManager();
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }

    protected function getManager()
    {
        $dir = sys_get_temp_dir().'/kb_git_repos';
        $manager = new RepoManager($dir, $_SERVER['GIT_BIN']);

        return $manager;
    }
}
