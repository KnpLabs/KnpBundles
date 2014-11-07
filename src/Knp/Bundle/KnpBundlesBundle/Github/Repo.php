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
use Github\Api\Repository\Contents;
use Github\Api\GitData\Trees;
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

        $activities       = $bundle->getLatestActivities();
        $lastEverCommitAt = $bundle->getLastCommitAt();

        /* @var $developer EntityDeveloper */
        foreach ($commits as $commit) {
            if (!isset($commit['commit']['committer'])) {
                continue;
            }

            $lastCommitAt = new \DateTime($commit['commit']['committer']['date']);
            $lastCommitAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            $activityData = array(
                'type'      => Activity::ACTIVITY_TYPE_COMMIT,
                'message'   => strtok($commit['commit']['message'], "\n\r"),
                'createdAt' => $lastCommitAt
            );

            if (!empty($activities)) {
                foreach ($activities as $registeredActivity) {
                    if (
                        $registeredActivity->getMessage() === $activityData['message'] &&
                        $registeredActivity->getCreatedAt() == $activityData['createdAt']
                    ) {
                        continue 2;
                    }
                }
            }

            // be sure that bundle have a latest date following to latest commit
            if ($lastCommitAt > $lastEverCommitAt) {
                $lastEverCommitAt = $lastCommitAt;
            }

            $activity = new Activity();
            $activity->setType($activityData['type']);
            $activity->setMessage($activityData['message']);
            $activity->setCreatedAt($activityData['createdAt']);
            $activity->setBundle($bundle);

            if (isset($commit['committer']) && isset($commit['committer']['login'])) {
                $developer = $this->ownerManager->createOwner($commit['committer']['login'], 'developer', true);
                $developer->setLastCommitAt($lastCommitAt);

                $activity->setDeveloper($developer);
            } else {
                $activity->setAuthor($commit['commit']['committer']['name']);
            }
        }

        // update last pushed commit date
        $bundle->setLastCommitAt(clone $lastEverCommitAt);

        if ('developer' === $bundle->getOwnerType()) {
            $bundle->getOwner()->setLastCommitAt(clone $lastEverCommitAt);
        }

        unset($activities);

        return true;
    }

    public function updateFiles(Bundle $bundle, array $onlyFiles = null)
    {
        $this->output->write(' files');

        /** @var \Github\Api\Repository\Contents $api */
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

                    try {
                        $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'LICENSE');
                        $bundle->setLicense(base64_decode($file['content']));
                    } catch(RuntimeException $e) {

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

                    try {
                        $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'composer.json');
                        $this->updateComposerFile(base64_decode($file['content']), $bundle);
                    } catch(RuntimeException $e) {

                    }

                    break;
            }
        }

        if (null === $onlyFiles || in_array('readme', $onlyFiles)) {
            try {
                $readme = $api->readme($bundle->getOwnerName(), $bundle->getName());
                if (!isset($readme['message']) && 'base64' == $readme['encoding']) {
                    $bundle->setReadme(base64_decode($readme['content']));
                }
            } catch (RuntimeException $e) {

            }
        }

        if (null === $bundle->getLicense() && (null === $onlyFiles || in_array('license', $onlyFiles))) {
            try {
                $file = $api->show($bundle->getOwnerName(), $bundle->getName(), 'Resources/meta/LICENSE');
                $bundle->setLicense(base64_decode($file['content']));
            } catch (RuntimeException $e) {}
        }

        if (null === $onlyFiles || in_array('configuration', $onlyFiles)) {
            try {
                $this->updateCanonicalConfigFile($bundle);
            } catch (RuntimeException $e) {

            }
        }

        try {
            $this->updateVersionsHistory($bundle);
        } catch (RuntimeException $e) {

        }

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
                'name'        => $value['name'],
                'extra'       => isset($value['extra']) ? $value['require-dev'] : '',
                'require'     => $value['require'],
                'require-dev' => isset($value['require-dev']) ? $value['require-dev'] : '',
                'suggest'     => isset($value['suggest']) ? $value['suggest'] : ''
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
        $valid = false;

        try {
            $contentApi = $this->github->api('repo')->contents();

            $valid = $this->tryToValidateWithComposerFile($bundle, $contentApi);

            if (!$valid) {
                $treeApi = $this->github->api('git')->trees();
                $valid   = $this->tryToValidateWithFiles($bundle, $treeApi, $contentApi);
            }
        } catch (RuntimeException $e) {
            $valid = false;
        }

        return $valid;
    }

    private function tryToValidateWithFiles(Bundle $bundle, Trees $treeApi, Contents $contentApi)
    {
        $valid = false;
        $tree  = $treeApi->show($bundle->getOwnerName(), $bundle->getName(), 'master', true);

        foreach ($tree['tree'] as $id => $fileData) {
            if ($fileData['path'] === 'app') {
                // this is a Symfony2 app, avoid it
                break;
            }

            if (false !== strpos($fileData['path'], 'Bundle.php')) {
                try {
                    $file = $contentApi->show($bundle->getOwnerName(), $bundle->getName(), $fileData['path']);
                    $fileContent = $this->decodeFileContent($file);
                    if ($this->containValidSymfonyReference($fileContent)) {
                        $valid = true;
                    }
                } catch(RuntimeException $e) {
                    $valid = false;
                }

                break;
            }
        }

        return $valid;
    }

    private function decodeFileContent($file, $json = false)
    {
        if ($json) {
            return json_decode(base64_decode($file['content']), true);
        }

        return base64_decode($file['content']);
    }

    private function containValidSymfonyReference($fileContent)
    {
        return false !== strpos($fileContent, 'Symfony\\Component\\HttpKernel\\Bundle\\Bundle');
    }

    private function tryToValidateWithComposerFile(Bundle $bundle, Contents $contentApi)
    {
        $validComposer = false;

        try {
            $file = $contentApi->show($bundle->getOwnerName(), $bundle->getName(), 'composer.json');

            $composer = $this->decodeFileContent($file, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                if (isset($composer['type']) && false !== strpos(strtolower($composer['type']), 'symfony')) {
                    $validComposer = true;
                }

                if (isset($composer['autoload']['psr-0'])) {
                    foreach ($composer['autoload']['psr-0'] as $key => $value) {
                        if (preg_match('/\\\\(.*)(\w*)Bundle$/', $key) > 0) {
                            $validComposer = true;
                        }
                    }
                }

                if (isset($composer['autoload']['psr-4'])) {
                    foreach ($composer['autoload']['psr-4'] as $key => $value) {
                        if (preg_match('/\\\\(.*)(\w*)Bundle$/', $key) > 0) {
                            $validComposer = true;
                        }
                    }
                }
            }
        } catch (RuntimeException $e) {
            $validComposer = false;
        }

        return $validComposer;
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
