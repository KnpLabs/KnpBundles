<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Github;

use Knp\Bundle\KnpBundlesBundle\Git\RepoManager;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Github\HttpClient\HttpClient;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateComposerFailure()
    {
        $repoEntity = new Bundle('knplabs/KnpMenuBundle');
        $repo = $this->getRepo();
        $gitRepo = $this->getGitRepoMock();

        $gitRepo->expects($this->any())
            ->method('hasFile')
            ->with('composer.json')
            ->will($this->returnValue(false));

        $method = new \ReflectionMethod($repo, 'updateComposerFile');
        $method->setAccessible(true);

        $method->invokeArgs($repo, array($gitRepo, $repoEntity));

        $this->assertNull($repoEntity->getComposerName());
    }

    public function testUpdateComposerSuccess()
    {
        $repoEntity = new Bundle('knplabs/KnpMenuBundle');
        $repo = $this->getRepo();
        $gitRepo = $this->getGitRepoMock();

        $gitRepo->expects($this->any())
            ->method('hasFile')
            ->with('composer.json')
            ->will($this->returnValue(true));

        $gitRepo->expects($this->any())
            ->method('getFileContent')
            ->with('composer.json')
            ->will($this->returnValue('{"name": "knplabs/knp-menu-bundle"}'));

        $method = new \ReflectionMethod($repo, 'updateComposerFile');
        $method->setAccessible(true);

        $method->invokeArgs($repo, array($gitRepo, $repoEntity));

        $this->assertEquals($repoEntity->getComposerName(), 'knplabs/knp-menu-bundle');
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnTRUEIfRepoHasCorrectBundleClass()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $repo = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');
        $this->assertTrue($repo->isValidSymfonyBundle($bundle));
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnFALSEIfRepoDoesNotHaveBundleClass()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $repo = $this->getRepoWithMockedGithubClient('Smth.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');
        $this->assertFalse($repo->isValidSymfonyBundle($bundle));
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnFALSEIfRepoHasIncorrectBundleClass()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $repo = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.invalid-bundle-class.json');
        $this->assertFalse($repo->isValidSymfonyBundle($bundle));
    }

    /**
     * @test
     */
    public function shouldUpdateCanonicalConfig()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');

        $githubRepo = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');

        $githubRepo->updateCanonicalConfigFile($this->getFullyMockedRepo($githubRepo, 'GoodBundle'), $bundle);

        $expectedYaml = <<<EOT
vendor_good_bundle:
    app_id:               ~ # Required
    secret:               ~ # Required
    file:                 ~
    cookie:               false
    domain:               ~
    alias:                ~
    logging:              %kernel.debug%
    culture:              en_US
    class:
        api:                  Vendor\\FixtureBundle\\APIKey
        type:                 Vendor\\FixtureBundle\\Type
    permissions:          []

EOT;

        $this->assertEquals($expectedYaml, $githubRepo->getCanonicalConfiguration());
    }

    /**
     * @test
     */
    public function shouldNotUpdateCanonicalConfig()
    {
        $bundle = new Bundle('knplabs/KnpMenuBundle');

        $githubRepo = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');

        $githubRepo->updateCanonicalConfigFile($this->getFullyMockedRepo($githubRepo, 'InvalidBundle'), $bundle);

        $this->assertEquals('', $githubRepo->getCanonicalConfiguration());
    }

    /**
     * @test
     */
    public function shouldUpdateSymfonyVersions()
    {
        $json = array('package' => array('versions' => array(
            0 => array('require' => array('symfony/framework-bundle' => 'dev-master', 'symfony/symfony' => 'dev-master')),
            1 => array('require' => array('symfony/framework-bundle' => '>=2.0,<2.2-dev', 'symfony/symfony' => '>=2.0,<2.2-dev'))
        )));

        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setComposerName('knplabs/knp-menu-bundle');

        $httpClient = $this->getMockBuilder('Github\HttpClient\HttpClient')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient->expects($this->once())
            ->method('get')
            ->will($this->returnValue($json));

        $githubRepo = $this->getRepo($httpClient);

        $githubRepo->updateSymfonyVersions($bundle);

        $this->assertCount(2, $bundle->getSymfonyVersions());
    }

    /**
     * @test
     */
    public function shoudNotUpdateSymfonyVersionsWithWrongData()
    {
        $json = 'I am wrong json';

        $bundle = new Bundle('knplabs/KnpMenuBundle');
        $bundle->setComposerName('knplabs/knp-menu-bundle');

        $httpClient = $this->getMockBuilder('Github\HttpClient\HttpClient')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMock();

        $httpClient->expects($this->once())
            ->method('get')
            ->will($this->returnValue($json));

        $githubRepo = $this->getRepo($httpClient);

        $githubRepo->updateSymfonyVersions($bundle);
    }

    protected function getRepo($httpClient = null)
    {
        $github = new \Github\Client($httpClient ? $httpClient : null);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $repoManager = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\RepoManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new Repo($github, $output, $repoManager, new EventDispatcher());
    }

    protected function getGitRepoMock()
    {
        return $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\Repo')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRepoWithMockedGithubClient($bundleClassName, $bundleClassContentsLink)
    {
        $githubApiRepoMock = $this->getMockBuilder('Github\Api\Repo')
            ->disableOriginalConstructor()
            ->getMock();
        $githubApiRepoMock->expects($this->any())
            ->method('contents')
            ->with('knplabs', 'KnpMenuBundle', '')
            ->will($this->returnValue(array(
                array(
                    'name'   => $bundleClassName,
                    '_links' => array(
                        'git' => $bundleClassContentsLink
                    )
                )
            )));

        $githubMock = $this->getMock('Github\Client');
        $githubMock->expects($this->any())
            ->method('api')
            ->with('repo')
            ->will($this->returnValue($githubApiRepoMock));
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $repoManager = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\RepoManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new Repo($githubMock, $output, $repoManager, new EventDispatcher());
    }

    protected function getFullyMockedRepo($githubRepo, $folder)
    {
        $gitRepo = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\Repo')
            ->disableOriginalConstructor()
            ->getMock();
        $gitRepo->expects($this->once())
            ->method('hasFile')
            ->will($this->returnValue(true));
        $gitRepo->expects($this->once())
            ->method('getDir')
            ->will($this->returnValue(__DIR__.'/../Fixtures/'.$folder));

        return $gitRepo;
    }
}
