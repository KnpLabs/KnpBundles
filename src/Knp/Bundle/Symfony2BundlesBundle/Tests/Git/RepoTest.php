<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Tests\Git;

use Knp\Bundle\Symfony2BundlesBundle\Git\RepoManager;
use Knp\Bundle\Symfony2BundlesBundle\Entity\Repo as RepoEntity;

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
        $manager = new RepoManager($dir, $_SERVER['GIT_BIN']);
        $repo = RepoEntity::create($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }
}
