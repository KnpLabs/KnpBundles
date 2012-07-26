<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

use Github\Client;
use Github\HttpClient\Exception as GithubException;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Git;
use Knp\Bundle\KnpBundlesBundle\Detector;
use Knp\Bundle\KnpBundlesBundle\Event\BundleEvent;

class Repo
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var \Github\Client|null
     */
    protected $github = null;

    /**
     * @var \Knp\Bundle\KnpBundlesBundle\Git\RepoManager|null
     */
    protected $gitRepoManager = null;

    /**
     * Output buffer
     *
     * @var null|\Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $dispatcher;

    protected $canonicalConfiguration;
    
    /**
     * @param \Github\Client $github
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Knp\Bundle\KnpBundlesBundle\Git\RepoManager $gitRepoManager
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(Client $github, OutputInterface $output, Git\RepoManager $gitRepoManager, EventDispatcherInterface $dispatcher)
    {
        $this->github = $github;
        $this->output = $output;
        $this->gitRepoManager = $gitRepoManager;
        $this->dispatcher = $dispatcher;
    }

    public function update(Bundle $bundle)
    {
        try {
            $this->gitRepoManager->getRepo($bundle)->update();
        } catch (\GitRuntimeException $e) {
            return false;
        }

        if (!$this->updateInfos($bundle)) {
            return false;
        }
        if (!$this->updateFiles($bundle)) {
            return false;
        }
        if (!$this->updateCommits($bundle)) {
            return false;
        }
        if (!$this->updateTags($bundle)) {
            return false;
        }

        $event = new BundleEvent($bundle);
        $this->dispatcher->dispatch(BundleEvent::UPDATE_SCORE, $event);
        $bundle->recalculateScore();

        return $bundle;
    }

    /**
     * Return true if the Repo exists on GitHub, false otherwise
     *
     * @param Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     * @param array $data
     * @return boolean whether the Repo exists on GitHub
     */
    public function updateInfos(Bundle $bundle)
    {
        $this->output->write(' infos');
        try {
            $data = $this->github->getRepoApi()->show($bundle->getUsername(), $bundle->getName());
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return false;
            }
            throw $e;
        }

        // Let's try to only keep a forked repo with lots of watchers
        if ($data['fork'] && $data['watchers'] < 10) {
            return false;
        }

        $bundle->setDescription(empty($data['description']) ? null : $data['description']);
        $bundle->setNbFollowers($data['watchers']);
        $bundle->setNbForks($data['forks']);
        $bundle->setCreatedAt(new \DateTime($data['created_at']));
        $bundle->setHomepage(empty($data['homepage']) ? null : $data['homepage']);

        return $bundle;
    }

    public function updateCommits(Bundle $bundle)
    {
        $this->output->write(' commits');
        try {
            $commits = $this->github->getCommitApi()->getBranchCommits($bundle->getUsername(), $bundle->getName(), 'HEAD');
            foreach ($commits as $key => $commit) {
                $commitDetailedInfo = $this->github->getCommitApi()->getCommit($bundle->getUsername(), $bundle->getName(), $commit['sha']);
                $commits[$key]['committer'] = $commitDetailedInfo['committer'];
            }
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return false;
            }
            throw $e;
        }
        if (empty($commits)) {
            return false;
        }
        $bundle->setLastCommits(array_slice($commits, 0, 30));

        return $bundle;
    }

    public function updateCommitsFromGitRepo(Bundle $bundle)
    {
        $this->output->write(' commits');
        $commits = $this->gitRepoManager->getRepo($bundle)->getCommits(30);
        $bundle->setLastCommits($commits);

        return $bundle;
    }

    public function updateFiles(Bundle $bundle)
    {
        $this->output->write(' files');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        foreach (array('README.markdown', 'README.md', 'README') as $readmeFilename) {
            if ($gitRepo->hasFile($readmeFilename)) {
               $bundle->setReadme($gitRepo->getFileContent($readmeFilename));
               break;
            }
        }

        foreach (array('LICENSE', 'Resources'.DIRECTORY_SEPARATOR.'meta'.DIRECTORY_SEPARATOR.'LICENSE') as $licenseFilename) {
            if ($gitRepo->hasFile($licenseFilename)) {
                $bundle->setLicense($gitRepo->getFileContent($licenseFilename));
                break;
            }
        }

        $bundle->setUsesTravisCi($gitRepo->hasFile('.travis.yml'));

        $this->updateComposerFile($gitRepo, $bundle);

        $this->updateCanonicalConfigFile($gitRepo, $bundle);

        $this->updateSymfonyVersions($bundle);

        return $bundle;
    }

    private function updateComposerFile($gitRepo, Bundle $bundle)
    {
        $composerFilename = 'composer.json';

        $composerName = null;
        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename), true);

            $composerName = isset($composer['name']) ? $composer['name'] : null;
        }

        $bundle->setComposerName($composerName);
    }

    public function updateSymfonyVersions(Bundle $bundle)
    {
        // retrieve name from composer.json
        $packagistName = $bundle->getComposerName();

        // no composer file
        if (null === $packagistName) {

            $bundle->setSymfonyVersions(null);
            return false;
        }

        $symfonyVersions = array();

        // query packagist json
        $packagistArray = json_decode(file_get_contents('http://packagist.org/packages/'.$packagistName.'.json'), true);

        // build array branch => version
        $versionsArray = $packagistArray['package']['versions'];
        foreach ($versionsArray as $version => $value) {

            foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {

                if (isset($value['require'][$requirement])) {
                    $symfonyVersions[$version] = $value['require'][$requirement]; // array('master' => >=2.0,<2.2-dev')
                }
            }
        }

        $bundle->setSymfonyVersions($symfonyVersions);
    }

    public function updateTags(Bundle $bundle)
    {
        $this->output->write(' tags');
        $gitRepo = $this->gitRepoManager->getRepo($bundle);
        $tags = $gitRepo->getGitRepo()->getTags();
        $bundle->setTags($tags);

        return $bundle;
    }

    public function fetchComposerKeywords(Bundle $bundle)
    {
        $composerFilename = 'composer.json';
        $gitRepo = $this->gitRepoManager->getRepo($bundle);

        if ($gitRepo->hasFile($composerFilename)) {
            $composer = json_decode($gitRepo->getFileContent($composerFilename));

            return isset($composer->keywords) ? $composer->keywords : array();
        }

        return array();
    }

    public function getContributorNames(Bundle $bundle)
    {
        try {
            $contributors = $this->github->getRepoApi()->getRepoContributors($bundle->getUsername(), $bundle->getName());
        } catch (GithubException $e) {
            if (404 === $e->getCode()) {
                return array();
            }
            throw $e;
        }
        $names = array();
        foreach ($contributors as $contributor) {
            if ($bundle->getUsername() != $contributor['login']) {
                $names[] = $contributor['login'];
            }
        }

        return $names;
    }

    /**
     * Checks if '*Bundle.php' class use base class for Symfony bundle
     *
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     *
     * @return bool
     */
    public function isValidSymfonyBundle(Bundle $bundle)
    {
        if (null === $bundleClassContent = $this->getBundleClassContentAsString($bundle)) {
            return false;
        }

        return false !== strpos($bundleClassContent, 'Symfony\Component\HttpKernel\Bundle\Bundle');
    }

    /**
     * Get output
     *
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Set output
     *
     * @param $output OutputInterface
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Get github
     *
     * @return \Github\Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param \Github\Client
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }

    /**
     * Returns content for *Bundle.php class via Github API v3
     *
     * @param \Knp\Bundle\KnpBundlesBundle\Entity\Bundle $bundle
     *
     * @return null|string
     */
    private function getBundleClassContentAsString(Bundle $bundle)
    {
        $rootContents = $this->github->getRepoApi()->getRepoContents($bundle->getUsername(), $bundle->getName(), '');
        foreach ($rootContents as $rootEntry) {
            if (strpos($rootEntry['name'], 'Bundle.php') !== false) {
                $response = json_decode(file_get_contents($rootEntry['_links']['git']));

                return base64_decode($response->content);
            }
        }

        return null;
    }

    public function updateCanonicalConfigFile($gitRepo, $bundle)
    {
        /**
         * Currently there is only support for bundles whose configuration is stored exactly under Configuration.php
         */
        $configurationRelativePath = 'DependencyInjection'.DIRECTORY_SEPARATOR.'Configuration.php';
        $configurationAbsolutePath = $gitRepo->getDir().DIRECTORY_SEPARATOR.$configurationRelativePath;
        $yamlContents = '';
        if ($gitRepo->hasFile($configurationRelativePath)) {

            include_once $configurationAbsolutePath;

            $tokens = token_get_all(file_get_contents($configurationAbsolutePath));
            $start = false;
            $namespace = '';
            foreach ($tokens as $token) {
                $tokenName = is_array($token) ? $token[0] : null;
                $tokenData = is_array($token) ? $token[1] : $token;
                if ($tokenName == T_NAMESPACE) {
                    $start = true;
                }
                if ($token == ';') {
                    $start = false;
                }
                if ($start == true && $tokenName != T_NAMESPACE && $tokenData != ' ') {
                    $namespace .= $tokenData;
                }
            }

            $configuration = $namespace.'\\Configuration';
            $configuration = new $configuration();

            // only dumps if it implements interface ConfigurationInterface
            if ($configuration instanceof ConfigurationInterface) {

                $rootNode = $configuration->getConfigTreeBuilder()->buildTree();
                $this->canonicalConfiguration = '';
                $this->outputNode($rootNode);
                $yamlContents = $this->canonicalConfiguration;
            }
        }

        $bundle->setCanonicalConfig($yamlContents);
    }

    /**
     * Outputs a single config reference line
     *
     * @param string $text
     * @param int    $indent
     */
    private function outputLine($text, $indent = 0)
    {
        $indent = strlen($text) + $indent;

        $format = '%'.$indent.'s';

        $this->canonicalConfiguration = $this->canonicalConfiguration . sprintf($format, $text) . "\n";
    }

    private function outputArray(array $array, $depth)
    {
        $isIndexed = array_values($array) === $array;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $val = '';
            } else {
                $val = $value;
            }

            if ($isIndexed) {
                $this->outputLine('- '.$val, $depth * 4);
            } else {
                $this->outputLine(sprintf('%-20s %s', $key.':', $val), $depth * 4);
            }

            if (is_array($value)) {
                $this->outputArray($value, $depth + 1);
            }
        }
    }

    /**
     * @param NodeInterface $node
     * @param int           $depth
     */
    private function outputNode(NodeInterface $node, $depth = 0)
    {
        $comments = array();
        $default = '';
        $defaultArray = null;
        $children = null;
        $example = $node->getExample();

        // defaults
        if ($node instanceof ArrayNode) {
            $children = $node->getChildren();

            if ($node instanceof PrototypedArrayNode) {
                $prototype = $node->getPrototype();

                if ($prototype instanceof ArrayNode) {
                    $children = $prototype->getChildren();
                }

                // check for attribute as key
                if ($key = $node->getKeyAttribute()) {
                    $keyNode = new ArrayNode($key, $node);
                    $keyNode->setInfo('Prototype');

                    // add children
                    foreach ($children as $childNode) {
                        $keyNode->addChild($childNode);
                    }
                    $children = array($key => $keyNode);
                }
            }

            if (!$children) {
                if ($node->hasDefaultValue() && count($defaultArray = $node->getDefaultValue())) {
                    $default = '';
                } elseif (!is_array($example)) {
                    $default = '[]';
                }
            }
        } else {
            $default = '~';

            if ($node->hasDefaultValue()) {
                $default = $node->getDefaultValue();

                if (true === $default) {
                    $default = 'true';
                } elseif (false === $default) {
                    $default = 'false';
                } elseif (null === $default) {
                    $default = '~';
                }
            }
        }

        // required?
        if ($node->isRequired()) {
            $comments[] = 'Required';
        }

        // example
        if ($example && !is_array($example)) {
            $comments[] = 'Example: '.$example;
        }

        $default = (string) $default != '' ? ' '.$default : '';
        $comments = count($comments) ? '# '.implode(', ', $comments) : '';

        $text = sprintf('%-20s %s %s', $node->getName().':', $default, $comments);

        if ($info = $node->getInfo()) {
            $this->outputLine('');
            $this->outputLine('# '.$info, $depth * 4);
        }

        $this->outputLine($text, $depth * 4);

        // output defaults
        if ($defaultArray) {
            $this->outputLine('');

            $message = count($defaultArray) > 1 ? 'Defaults' : 'Default';

            $this->outputLine('# '.$message.':', $depth * 4 + 4);

            $this->outputArray($defaultArray, $depth + 1);
        }

        if (is_array($example)) {
            $this->outputLine('');

            $message = count($example) > 1 ? 'Examples' : 'Example';

            $this->outputLine('# '.$message.':', $depth * 4 + 4);

            $this->outputArray($example, $depth + 1);
        }

        if ($children) {
            foreach ($children as $childNode) {
                $this->outputNode($childNode, $depth + 1);
            }
        }
    }

    public function getCanonicalConfiguration()
    {
        return $this->canonicalConfiguration;
    }

    public function setCanonicalConfiguration($canonicalConfiguration)
    {
        $this->canonicalConfiguration = $canonicalConfiguration;
    }
}
