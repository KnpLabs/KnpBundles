<?php

namespace Application\S2bBundle\Tests\Git;

use Application\S2bBundle\Git\RepoManager;
use Application\S2bBundle\Git\Repo;
use Application\S2bBundle\Entity\Repo as RepoEntity;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGitRepo()
    {
        $repo = $this->getRepo();
        $this->assertTrue($repo->getGitRepo() instanceof \phpGitRepo);
    }

    protected function getRepo($repoFullName = 'knplabs/DoctrineUserBundle')
    {
        $dir = sys_get_temp_dir().'/s2b_git_repos';
        $manager = new RepoManager($dir);
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }
}
