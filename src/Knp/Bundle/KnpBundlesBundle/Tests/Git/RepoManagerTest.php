<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Git;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle as BundleEntity;
use Symfony\Component\Filesystem\Filesystem;
use PHPGit_Repository;

class RepoManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->cleanUpDirectories();
    }

    public function tearDown()
    {
        $this->cleanUpDirectories();
    }

    /**
     * @test
     */
    public function shouldCreateAndCloneNewRepoWhenNotExists()
    {
        $bundle = new BundleEntity();
        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $expectedDir = $this->getExpectedDir();

        $repoManager = $this->getRepoManager();
        $repoManager->expects($this->once())
            ->method('cloneRepo')
            ->with($this->equalTo('git://github.com/KnpLabs/KnpBundles.git'), $this->equalTo($expectedDir))
            ->will($this->returnValue($this->getPhpGitRepository()));

        $this->assertFalse(is_dir($expectedDir));
        $this->assertInstanceOf('Knp\Bundle\KnpBundlesBundle\Git\Repo', $repoManager->getRepo($bundle));
        $this->assertTrue(is_dir($expectedDir));
    }

    /**
     * @test
     */
    public function shouldNotCreateNewRepoWhenExists()
    {
        $bundle = new BundleEntity();
        $bundle->setUsername('KnpLabs');
        $bundle->setName('KnpBundles');

        $expectedDir = $this->getExpectedDir();
        $this->createRepoInExpectedLocation();

        $repoManager = $this->getRepoManager();
        $repoManager->expects($this->never())
            ->method('cloneRepo');

        $this->assertTrue(is_dir($expectedDir));
        $this->assertInstanceOf('Knp\Bundle\KnpBundlesBundle\Git\Repo', $repoManager->getRepo($bundle));
    }

    private function getRepoManager()
    {
        return $this->getMock('Knp\Bundle\KnpBundlesBundle\Git\RepoManager',
            array('cloneRepo'),
            array(new Filesystem(), $this->getDir(), 'git')
        );
    }

    private function createRepoInExpectedLocation()
    {
        mkdir($this->getExpectedDir() . DIRECTORY_SEPARATOR . '.git', 0777, true);
        file_put_contents($this->getExpectedDir() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD', ' ');
    }

    private function cleanUpDirectories()
    {
        @unlink($this->getExpectedDir() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'HEAD');
        @rmdir($this->getExpectedDir() . DIRECTORY_SEPARATOR . '.git');
        @rmdir($this->getExpectedDir());
        @rmdir($this->getDir() . DIRECTORY_SEPARATOR . 'KnpLabs');
        @rmdir($this->getDir());
    }

    private function getDir()
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'kb_git_repos';
    }

    private function getExpectedDir()
    {
        return $this->getDir() . DIRECTORY_SEPARATOR . 'KnpLabs' . DIRECTORY_SEPARATOR . 'KnpBundles';
    }

    private function getPhpGitRepository()
    {
      return $this->getMockBuilder('PHPGit_Repository')
          ->disableOriginalConstructor()
          ->getMock();
    }
}
