<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\PhpProcess;

use Github\Client;
use Github\Exception\RuntimeException;

use Knp\Bundle\KnpBundlesBundle\Entity\Activity;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;
use Knp\Bundle\KnpBundlesBundle\Event\BundleEvent;
use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;
use Knp\Bundle\KnpBundlesBundle\Git;

class Repo
{
    /**
     * php-github-api instance used to request GitHub API
     *
     * @var Client
     */
    private $github;

    /**
     * @var Git\RepoManager
     */
    private $gitRepoManager;

    /**
     * Output buffer
     *
     * @var OutputInterface
     */
    private $output;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var OwnerManager
     */
    private $ownerManager;

    /**
     * @var string
     */
    public static $canonicalConfiguration;

    /**
     * @param Client                   $github
     * @param OutputInterface          $output
     * @param Git\RepoManager          $gitRepoManager
     * @param EventDispatcherInterface $dispatcher
     * @param OwnerManager             $ownerManager
     */
    public function __construct(Client $github, OutputInterface $output, Git\RepoManager $gitRepoManager, EventDispatcherInterface $dispatcher, OwnerManager $ownerManager)
    {
        $this->github = $github;
        $this->output = $output;
        $this->gitRepoManager = $gitRepoManager;
        $this->dispatcher = $dispatcher;
        $this->ownerManager = $ownerManager;
    }

    /**
     * @param Bundle $bundle
     *
     * @return boolean|Bundle
     */
    public function update(Bundle $bundle)
    {
        try {
            $this->gitRepoManager->getRepo($bundle)->update();
        } catch (\GitRuntimeException $e) {
            return false;
        }

        if (!$this->updateFiles($bundle)) {
            return false;
        }
        if (!$this->updateInfos($bundle)) {
            return false;
        }
        if (!$this->updateCommits($bundle)) {
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
            $data = $this->github->api('repo')->show($bundle->getOwnerName(), $bundle->getName());
        } catch(RuntimeException $e) {
            return false;
        }

        // Let's try to only keep a forked repo with lots of watchers
        if ($data['fork'] && $data['watchers'] < 10) {
            return false;
        }

        $bundle->setDescription(empty($data['description']) ? null : $data['description']);
        $bundle->setNbFollowers($data['watchers']);
        $bundle->setNbForks($data['forks']);
        $bundle->setIsFork($data['fork']);
        $bundle->setCreatedAt(new \DateTime($data['created_at']));
        $bundle->setHomepage(empty($data['homepage']) ? null : $data['homepage']);

        return true;
    }

    public function updateCommits(Bundle $bundle)
    {
        $this->output->write(' commits');

        try {
            $commits = $this->github->api('repo')->commits()->all($bundle->getOwnerName(), $bundle->getName(), array('sha' => 'HEAD', 'per_page' => 30));
        } catch(RuntimeException $e) {
            return false;
        }

        $activities = $bundle->getLatestActivities();

        /* @var $developer EntityDeveloper */
        foreach ($commits as $commit) {
            if (!isset($commit['commit']['committer'])) {
                continue;
            }

            $lastCommitAt = new \DateTime();
            $lastCommitAt->setTimestamp(strtotime($commit['commit']['committer']['date']));

            /* @var $activity Activity */
            if ($activities) {
                foreach ($activities as $key => $activity) {
                    // If both activities have same type and time, skip (and "hide" it) as this is probably duplicate
                    if ($lastCommitAt->getTimestamp() == $activity->getCreatedAt()->getTimestamp()) {
                        unset($activities[$key]);

                        continue 2;
                    }
                }
            }

            $activity = new Activity();
            $activity->setType(Activity::ACTIVITY_TYPE_COMMIT);
            $activity->setMessage(strtok($commit['commit']['message'], "\n\r"));
            $activity->setCreatedAt($lastCommitAt);
            $activity->setBundle($bundle);

            if (isset($commit['committer']) && isset($commit['committer']['login'])) {
                $developer = $this->ownerManager->createOwner($commit['committer']['login'], 'developer', false);
                $developer->setLastCommitAt($lastCommitAt);

                $activity->setDeveloper($developer);
            } else {
                $activity->setAuthor($commit['commit']['committer']['name']);
            }
        }

        unset($activities);

        return true;
    }

    public function updateFiles(Bundle $bundle, array $onlyFiles = null)
    {
        $this->output->write(' files');

        $api = $this->github->api('repo')->contents();

        try {
            $files = $api->show($bundle->getOwnerName(), $bundle->getName());
        } catch(RuntimeException $e) {
            return false;
        }

        foreach ($files as $data) {
            switch ($data['name']) {
                case 'LICENSE':
                    if (null !== $onlyFiles && !in_array('license', $onlyFiles)) {
                        continue;
                    }

                    $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'LICENSE');
                    if (!isset($file['message']) && 'base64' == $file['encoding']) {
                        $bundle->setLicense(base64_decode($file['content']));
                        break;
                    }
                    break;

                case '.travis.yml':
                    if (null !== $onlyFiles && !in_array('travis', $onlyFiles)) {
                        continue;
                    }

                    $bundle->setUsesTravisCi(true);
                    break;

                case 'composer.json':
                    if (null !== $onlyFiles && !in_array('composer', $onlyFiles)) {
                        continue;
                    }

                    $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'composer.json');
                    if (!isset($file['message']) && 'base64' == $file['encoding']) {
                        $this->updateComposerFile(base64_decode($file['content']), $bundle);
                        break;
                    }
            }
        }

        if (null === $onlyFiles || in_array('readme', $onlyFiles)) {
            $readme = $api->readme($bundle->getOwnerName(), $bundle->getName());
            if (!isset($readme['message']) && 'base64' == $readme['encoding']) {
                $bundle->setReadme(base64_decode($readme['content']));
            }
        }

        if (null === $bundle->getLicense() && (null === $onlyFiles || in_array('license', $onlyFiles))) {
            $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'Resources/meta/LICENSE');
            if (!isset($file['message']) && 'base64' == $file['encoding']) {
                $bundle->setLicense(base64_decode($file['content']));
            }
        }

        if (null === $onlyFiles || in_array('configuration', $onlyFiles)) {
            $this->updateCanonicalConfigFile($bundle);
        }

        $this->updateVersionsHistory($bundle);

        return true;
    }

    /**
     * @param string $composer
     * @param Bundle $bundle
     */
    private function updateComposerFile($composer, Bundle $bundle)
    {
        $composer = json_decode($composer, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return;
        }

        if (isset($composer['name'])) {
            $bundle->setComposerName($composer['name']);
        }
        if (isset($composer['license'])) {
            $bundle->setLicenseType(is_array($composer['license']) ? implode(', ', $composer['license']) : $composer['license']);
        }
    }

    // @deprecated use updateVersionsHistory
    public function updateSymfonyVersions(Bundle $bundle)
    {
        // no composer file
        if (null === $composerName = $bundle->getComposerName()) {
            return false;
        }

        // query packagist json
        $packagistArray = $this->github->getHttpClient()->get($composerName, array(), array('url' => 'http://packagist.org/packages/:path.json'));

        // if json not encoded
        if (!is_array($packagistArray) || !isset($packagistArray['package'])) {
            return false;
        }

        $symfonyVersions = array();
        $versionsArray = $packagistArray['package']['versions'];

        foreach ($versionsArray as $version => $value) {
            foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {
                if (isset($value['require'][$requirement])) {
                    // Skip `dev` packages, add only `dev-master`
                    if (0 === strpos($version, 'dev-') && 'dev-master' != $version) {
                        continue;
                    }
                    $symfonyVersions[$version] = $value['require'][$requirement]; // array('master' => '>=2.0,<2.2-dev')
                }
            }
        }

        if (!empty($symfonyVersions)) {
            $bundle->setSymfonyVersions($symfonyVersions);
        }

        return true;
    }

    public function updateVersionsHistory(Bundle $bundle)
    {
        // no composer file
        if (null === $composerName = $bundle->getComposerName()) {
            return false;
        }

        // query packagist json
        $packagistArray = $this->github->getHttpClient()->get($composerName, array(), array('url' => 'http://packagist.org/packages/:path.json'));

        // if json not encoded
        if (!is_array($packagistArray) || !isset($packagistArray['package'])) {
            return false;
        }

        $versionsHistory = array();
        $versionsArray = $packagistArray['package']['versions'];

        foreach ($versionsArray as $version => $value) {
            // Skip `dev` packages, add only `dev-master`
            if (0 === strpos($version, 'dev-') && 'dev-master' != $version) {
                continue;
            }

            foreach (array('symfony/framework-bundle', 'symfony/symfony') as $requirement) {
                if (isset($value['require'][$requirement])) {
                    $versionsHistory['symfony'][$version] = $value['require'][$requirement]; // array('master' => '>=2.0,<2.2-dev')
                }
            }

            // store all bundle dependencies
            $versionsHistory['dependencies'][$version] = array(
                'require' => $value['require'],
                'require-dev' => isset($value['require-dev']) ? $value['require-dev'] : '',
                'suggest' => isset($value['suggest']) ? $value['suggest'] : ''
            );
        }

        if (!empty($versionsHistory)) {
            $bundle->setVersionsHistory($versionsHistory);
        }

        return true;
    }  

    public function fetchComposerKeywords(Bundle $bundle)
    {
        try {
            $file = $this->github->api('repo')->contents()->show($bundle->getOwnerName(), $bundle->getName(), 'composer.json');

            if ('base64' == $file['encoding']) {
                $composer = json_decode(base64_decode($file['content']), true);
                if (JSON_ERROR_NONE === json_last_error()) {
                    return isset($composer['keywords']) ? $composer['keywords'] : array();
                }
            }
        } catch(RuntimeException $e) {
        }

        return array();
    }

    public function getContributorNames(Bundle $bundle)
    {
        try {
            $contributors = $this->github->api('repo')->contributors($bundle->getOwnerName(), $bundle->getName());
        } catch(RuntimeException $e) {
            return array();
        }

        $names = array();
        foreach ($contributors as $contributor) {
            if ($bundle->getOwnerName() != $contributor['login']) {
                $names[] = $contributor['login'];
            }
        }

        return $names;
    }

    /**
     * @param Bundle $bundle
     *
     * @return boolean
     */
    public function validate(Bundle $bundle)
    {
        $api = $this->github->api('repo')->contents();
        try {
            $files = $api->show($bundle->getOwnerName(), $bundle->getName());
        } catch(RuntimeException $e) {
            return false;
        }

        foreach ($files as $data) {
            if (false !== strpos($data['name'], 'Bundle.php')) {
                try {
                    $file = $api->show($bundle->getOwnerName(), $bundle->getName(), $data['name']);
                    if ('base64' == $file['encoding']) {
                        return false !== strpos(base64_decode($file['content']), 'Symfony\\Component\\HttpKernel\\Bundle\\Bundle');
                    }
                } catch(RuntimeException $e) {
                    return false;
                }

                break;
            }
        }

        return false;
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

    public function updateCanonicalConfigFile(Bundle $bundle)
    {
        self::$canonicalConfiguration = '';

        $gitRepo = $this->gitRepoManager->getRepo($bundle);

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
