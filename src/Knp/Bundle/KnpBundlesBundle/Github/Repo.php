<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\Process\PhpProcess;

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
     * @var Client|null
     */
    protected $github = null;

    /**
     * @var Git\RepoManager|null
     */
    protected $gitRepoManager = null;

    /**
     * Output buffer
     *
     * @var null|OutputInterface
     */
    protected $output = null;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string
     */
    public static $canonicalConfiguration;

    /**
     * @param Client $github
     * @param OutputInterface $output
     * @param Git\RepoManager $gitRepoManager
     * @param EventDispatcherInterface $dispatcher
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
     * @param Bundle $bundle
     *
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

        return true;
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

        return true;
    }

    public function updateCommitsFromGitRepo(Bundle $bundle)
    {
        $this->output->write(' commits');
        $commits = $this->gitRepoManager->getRepo($bundle)->getCommits(30);
        $bundle->setLastCommits($commits);

        return true;
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

        return true;
    }

    private function updateComposerFile($gitRepo, Bundle $bundle)
    {
        if ($gitRepo->hasFile('composer.json')) {
            $composer = json_decode($gitRepo->getFileContent('composer.json'), true);

            $composerName = isset($composer['name']) ? $composer['name'] : null;

            // looking for required version of Symfony
            if (isset($composer['require'])) {
                foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {
                    if (isset($composer['require'][$requirement])) {
                        $bundle->setSymfonyVersion($composer['require'][$requirement]);
                        break;
                    }
                }
            }

            $bundle->setComposerName($composerName);
        }
    }

    public function updateSymfonyVersions(Bundle $bundle)
    {
        // no composer file
        if (null === $bundle->getComposerName()) {
            return false;
        }

        $symfonyVersions = array();

        // query packagist json
        $packagistArray = $this->github->getHttpClient()->get($bundle->getPackagistUrl().'.json');

        // if json not encoded
        if (!is_array($packagistArray)) {
            return false;
        }

        // build array branch => version
        $versionsArray = $packagistArray['package']['versions'];

        foreach ($versionsArray as $version => $value) {
            foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {
                if (isset($value['require'][$requirement])) {
                    $symfonyVersions[$version] = $value['require'][$requirement]; // array('master' => '>=2.0,<2.2-dev')
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
     * @param Bundle $bundle
     *
     * @return bool
     */
    public function isValidSymfonyBundle(Bundle $bundle)
    {
        if (null === $bundleClassContent = $this->getBundleClassContentAsString($bundle)) {
            return false;
        }

        return false !== strpos($bundleClassContent, 'Symfony\\Component\\HttpKernel\\Bundle\\Bundle');
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
     * @return Client
     */
    public function getGithubClient()
    {
        return $this->github;
    }

    /**
     * Set github
     *
     * @param Client
     */
    public function setGithubClient($github)
    {
        $this->github = $github;
    }

    public function updateCanonicalConfigFile($gitRepo, Bundle $bundle)
    {
        self::$canonicalConfiguration = '';

        /**
         * Currently there is only support for bundles whose configuration is stored exactly under Configuration.php
         */
        $relativePath = 'DependencyInjection'.DIRECTORY_SEPARATOR.'Configuration.php';
        if ($gitRepo->hasFile($relativePath)) {
            $absolutePath = $gitRepo->getDir().DIRECTORY_SEPARATOR.$relativePath;

            $tokens    = token_get_all(file_get_contents($absolutePath));
            $start     = false;
            $namespace = '';
            foreach ($tokens as $token) {
                if ($token == ';') {
                    break;
                }

                $tokenName = is_array($token) ? $token[0] : null;
                if (T_NAMESPACE === $tokenName) {
                    $start = true;

                    continue;
                }

                // Still not found namespace, skip this part of code
                if ($start === false) {
                    continue;
                }

                $tokenData = is_array($token) ? $token[1] : $token;
                if ($tokenData == ' ') {
                    continue;
                }

                $namespace .= $tokenData;
            }
            unset($tokens);

            $autoloaderPath = __DIR__.'/../../../../../vendor/autoload.php';

            $script = <<<EOF
<?php

include_once "$autoloaderPath";
include_once "$absolutePath";

use Knp\Bundle\KnpBundlesBundle\Github\Repo;

\$configuration = new \ReflectionClass("$namespace\\Configuration");
// only dumps if it implements interface ConfigurationInterface
if (in_array('Symfony\\Component\\Config\\Definition\\ConfigurationInterface', \$configuration->getInterfaceNames())) {
    \$configuration = \$configuration->newInstance();
    \$configuration = Repo::outputNode(\$configuration->getConfigTreeBuilder()->buildTree());

    echo Repo::\$canonicalConfiguration;
} else {
    echo '';
}

?>
EOF;

            // Workaround for bundles with external deps called in DI configuration, i.e. FOSRestBundle
            $process = new PhpProcess($script);
            $process->run();

            if ($process->isSuccessful()) {
                $bundle->setCanonicalConfig(Repo::$canonicalConfiguration = $process->getOutput());
            }
        }
    }

    public function getCanonicalConfiguration()
    {
        return self::$canonicalConfiguration;
    }

    public function setCanonicalConfiguration($canonicalConfiguration)
    {
        self::$canonicalConfiguration = $canonicalConfiguration;
    }

    /**
     * Returns content for *Bundle.php class via Github API v3
     *
     * @param Bundle $bundle
     *
     * @return null|string
     */
    private function getBundleClassContentAsString(Bundle $bundle)
    {
        $rootContents = $this->github->getRepoApi()->getRepoContents($bundle->getUsername(), $bundle->getName(), '');
        foreach ($rootContents as $rootEntry) {
            if (isset($rootEntry['name']) && strpos($rootEntry['name'], 'Bundle.php') !== false) {
                $response = json_decode(file_get_contents($rootEntry['_links']['git']));

                return base64_decode($response->content);
            }
        }

        return null;
    }

    /**
     * Outputs a single config reference line
     *
     * @param string  $text
     * @param integer $indent
     */
    public static function outputLine($text, $indent = 0)
    {
        $indent = strlen($text) + $indent;

        self::$canonicalConfiguration .= rtrim(sprintf('%'.$indent.'s', $text)) . "\n";
    }

    public static function outputArray(array $array, $depth)
    {
        $isIndexed = array_values($array) === $array;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $val = '';
            } else {
                $val = $value;
            }

            if ($isIndexed) {
                self::outputLine('- '.$val, $depth * 4);
            } else {
                self::outputLine(sprintf('%-20s %s', $key.':', $val), $depth * 4);
            }

            if (is_array($value)) {
                self::outputArray($value, $depth + 1);
            }
        }
    }

    /**
     * @param NodeInterface $node
     * @param integer       $depth
     */
    public static function outputNode(NodeInterface $node, $depth = 0)
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
            self::outputLine('');
            self::outputLine('# '.$info, $depth * 4);
        }

        self::outputLine($text, $depth * 4);

        // output defaults
        if ($defaultArray) {
            self::outputLine('');

            $message = count($defaultArray) > 1 ? 'Defaults' : 'Default';

            self::outputLine('# '.$message.':', $depth * 4 + 4);

            self::outputArray($defaultArray, $depth + 1);
        }

        if (is_array($example)) {
            self::outputLine('');

            $message = count($example) > 1 ? 'Examples' : 'Example';

            self::outputLine('# '.$message.':', $depth * 4 + 4);

            self::outputArray($example, $depth + 1);
        }

        if ($children) {
            foreach ($children as $childNode) {
                self::outputNode($childNode, $depth + 1);
            }
        }
    }
}
