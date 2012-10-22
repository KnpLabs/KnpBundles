<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Github;

use Knp\Bundle\KnpBundlesBundle\Github\Repo;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Github\HttpClient\HttpClient;

class RepoTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateComposerFailure()
    {
        $repoEntity = new Bundle('KnpLabs/KnpMenuBundle');

        $repo   = $this->getRepo();
        $method = new \ReflectionMethod($repo, 'updateComposerFile');
        $method->setAccessible(true);
        $method->invokeArgs($repo, array('invalid json data', $repoEntity));

        $this->assertNull($repoEntity->getComposerName());
    }

    public function testUpdateComposerSuccess()
    {
        $repoEntity = new Bundle('KnpLabs/KnpMenuBundle');

        $composer = json_decode(file_get_contents(__DIR__ . '/fixtures/encoded-composer.json'), true);
        $composer = base64_decode($composer['content']);

        $repo   = $this->getRepo();
        $method = new \ReflectionMethod($repo, 'updateComposerFile');
        $method->setAccessible(true);
        $method->invokeArgs($repo, array($composer, $repoEntity));

        $this->assertEquals($repoEntity->getComposerName(), 'knplabs/knp-menu-bundle');
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnTRUEIfRepoHasCorrectBundleClass()
    {
        $bundle = new Bundle('KnpLabs/KnpMenuBundle');
        $repo   = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');

        $this->assertTrue($repo->validate($bundle));
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnFALSEIfRepoDoesNotHaveBundleClass()
    {
        $bundle = new Bundle('KnpLabs/KnpMenuBundle');
        $repo   = $this->getRepoWithMockedGithubClient('Smth.php', __DIR__ . '/fixtures/info.valid-bundle-class.json');

        $this->assertFalse($repo->validate($bundle));
    }

    /**
     * @test
     */
    public function isValidSymfonyBundleShouldReturnFALSEIfRepoHasIncorrectBundleClass()
    {
        $bundle = new Bundle('KnpLabs/KnpMenuBundle');
        $repo   = $this->getRepoWithMockedGithubClient('KnpMenuBundle.php', __DIR__ . '/fixtures/info.invalid-bundle-class.json');

        $this->assertFalse($repo->validate($bundle));
    }

    /**
     * @test
     */
    public function shouldUpdateCanonicalConfig()
    {
        $bundle = new Bundle('KnpLabs/GoodBundle');

        $githubRepo = $this->getRepoWithMockedGitRepo($bundle);
        $githubRepo->updateCanonicalConfigFile($bundle);

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
        $bundle = new Bundle('KnpLabs/InvalidBundle');

        $githubRepo = $this->getRepoWithMockedGitRepo($bundle);
        $githubRepo->updateCanonicalConfigFile($bundle);

        $this->assertEquals('', $githubRepo->getCanonicalConfiguration());
    }

    /**
     * @test
     */
    public function shouldUpdateSymfonyVersions()
    {
        $json = array(
            'package' => array(
                'versions' => array(
                    array('require' => array('symfony/framework-bundle' => 'dev-master', 'symfony/symfony' => 'dev-master')),
                    array('require' => array('symfony/framework-bundle' => '>=2.0,<2.2-dev', 'symfony/symfony' => '>=2.0,<2.2-dev'))
                )
            )
        );

        $bundle = new Bundle('KnpLabs/KnpMenuBundle');
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

        $bundle = new Bundle('KnpLabs/KnpMenuBundle');
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

        $this->assertNull($bundle->getSymfonyVersions());
    }

    protected function getRepo($httpClient = null)
    {
        $github = new \Github\Client();
        if ($httpClient) {
            $github->setHttpClient($httpClient);
        }

        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $repoManager = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\RepoManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new Repo($github, $output, $repoManager, new EventDispatcher(), $this->getOwnerManagerMock());
    }

    protected function getRepoWithMockedGithubClient($bundleClassName, $bundleClassContentsLink)
    {
        $json = json_decode(file_get_contents($bundleClassContentsLink), true);

        $githubApiContentsMock = $this->getMockBuilder('Github\Api\Repository\Contents')
            ->disableOriginalConstructor()
            ->getMock();
        $githubApiContentsMock->expects($this->at(0))
            ->method('show')
            ->with('KnpLabs', 'KnpMenuBundle')
            ->will($this->returnValue(array(
                array(
                    'name'     => $bundleClassName,
                    'encoding' => 'base64',
                    'content'  => $json
                )
            )));

        if (false !== strpos($bundleClassName, 'KnpMenuBundle')) {
            $githubApiContentsMock->expects($this->at(1))
                ->method('show')
                ->with('KnpLabs', 'KnpMenuBundle', $bundleClassName)
                ->will($this->returnValue($json));
        }

        $githubApiMock = $this->getMockBuilder('Github\Api\Repo')
            ->disableOriginalConstructor()
            ->getMock();
        $githubApiMock->expects($this->any())
            ->method('contents')
            ->will($this->returnValue($githubApiContentsMock));

        $githubMock = $this->getMock('Github\Client');
        $githubMock->expects($this->any())
            ->method('api')
            ->with('repo')
            ->will($this->returnValue($githubApiMock));
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $repoManager = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\RepoManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new Repo($githubMock, $output, $repoManager, new EventDispatcher(), $this->getOwnerManagerMock());
    }

    protected function getRepoWithMockedGitRepo(Bundle $bundle)
    {
        $githubMock  = $this->getMock('Github\Client');
        $output      = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $repoManager = $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\RepoManager')
            ->disableOriginalConstructor()
            ->getMock();
        $repoManager->expects($this->any())
            ->method('getRepo')
            ->with($bundle)
            ->will($this->returnValue($this->getFullyMockedRepo($bundle->getName())));

        return new Repo($githubMock, $output, $repoManager, new EventDispatcher(), $this->getOwnerManagerMock());
    }

    /**
     * @param string $folder
     *
     * @return Repo
     */
    protected function getFullyMockedRepo($folder)
    {
        $gitRepo = $this->getGitRepoMock();
        $gitRepo->expects($this->once())
            ->method('hasFile')
            ->will($this->returnValue(true));
        $gitRepo->expects($this->once())
            ->method('getDir')
            ->will($this->returnValue(__DIR__.'/../Fixtures/'.$folder));

        return $gitRepo;
    }

    protected function getOwnerManagerMock()
    {
        return $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getGitRepoMock()
    {
        return $this->getMockBuilder('Knp\Bundle\KnpBundlesBundle\Git\Repo')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
