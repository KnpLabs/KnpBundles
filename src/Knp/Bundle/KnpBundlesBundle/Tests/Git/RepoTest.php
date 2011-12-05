<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGitRepo()
    {
        $repo = $this->getRepo();
        $this->assertTrue($repo->getGitRepo() instanceof \PHPGit_Repository);
    }

    protected function getRepo($repoFullName = 'KnpLabs/KnpGaufretteBundle')
    {
        $dir = sys_get_temp_dir().'/kb_git_repos';
        $manager = new RepoManager($dir, $_SERVER['GIT_BIN']);
        $repo = new Bundle($repoFullName);
        $gitRepo = $manager->getRepo($repo);

        return $gitRepo;
    }
}
