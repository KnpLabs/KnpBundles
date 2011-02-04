<?php

namespace Knplabs\Symfony2BundlesBundle\Tests\Git;

use Knplabs\Symfony2BundlesBundle\Git\RepoManager;
use Knplabs\Symfony2BundlesBundle\Git\Repo;
use Knplabs\Symfony2BundlesBundle\Entity\Repo as RepoEntity;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGitRepo()
    {
        $repo = $this->getRepo();
        $this->assertTrue($repo->getGitRepo() instanceof \phpGitRepo);
    }

    protected function getRepo($repoFullName = 'FriendsOfSymfony/UserBundle')
    {
        $dir = sys_get_temp_dir().'/s2b_git_repos';
        $manager = new RepoManager($dir);
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }
}
