<?php

namespace Application\S2bBundle\Tests\Git;

use Application\S2bBundle\Git\RepoManager;
use Application\S2bBundle\Git\Repo;
use Application\S2bBundle\Entity\Repo as RepoEntity;

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

    protected function getRepo($repoFullName = 'knplabs/DoctrineUserBundle')
    {
        $manager = $this->getManager();
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }

    protected function getManager()
    {
        $dir = sys_get_temp_dir().'/s2b_git_repos';
        $manager = new RepoManager($dir);

        return $manager;
    }
}
