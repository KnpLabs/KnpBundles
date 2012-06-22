<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;

use Behat\MinkBundle\Driver\SymfonyDriver;

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\Step,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Mink\Exception\ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Exception\ResponseTextException,
    Behat\Mink\Exception\ElementHtmlException,
    Behat\Mink\Exception\ElementTextException,
    Behat\Mink\Exception\UnsupportedDriverActionException;

use Etcpasswd\OAuthBundle\Security\Core\Authentication\Token\OAuthToken,
    Etcpasswd\OAuthBundle\Provider\Token\GithubToken;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use PHPUnit_Framework_ExpectationFailedException as AssertException;
use Knp\Bundle\KnpBundlesBundle\Entity;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext
{
    private $users;
    private $bundles;
    private $keywords;

    public function __construct($kernel)
    {
        $this->useContext('symfony_doctrine', new \Behat\CommonContexts\SymfonyDoctrineContext($kernel));
        $this->useContext('solr', new \Knp\Bundle\KnpBundlesBundle\Features\Context\SolrContext($kernel));

        parent::__construct($kernel);
    }

    /**
     * @Given /^the site has following users:$/
     */
    public function theSiteHasFollowingUsers(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        $this->users = array();
        foreach ($table->getHash() as $row) {
            $user = new Entity\User();

            $user->fromArray(array(
                'name'          => $row['name'],
                'score'         => 0,
            ));

            $entityManager->persist($user);

            $this->users[$user->getName()] = $user;
        }

        $entityManager->flush();
    }

    /**
     * @Given /^the site has following bundles:$/
     */
    public function theSiteHasFollowingBundles(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        $this->bundles = array();
        foreach ($table->getHash() as $row) {
            $user = $this->users[$row['username']];

            $bundle = new Entity\Bundle();
            $bundle->fromArray(array(
                'name'          => $row['name'],
                'user'          => $user,
                'username'      => $user->getName(),
                'description'   => $row['description'],
                'state'         => isset($row['state']) ? $row['state'] : Entity\Bundle::STATE_UNKNOWN,
                'lastCommitAt'  => new \DateTime($row['lastCommitAt']),
            ));

            $bundle->setScore($row['score']);

            $this->setPrivateProperty($bundle, "trend1", $row['trend1']);

            if (isset($row['recommendedBy'])) {
                $usernames = explode(',', $row['recommendedBy']);
                foreach ($usernames as $username) {
                    $user = $this->users[trim($username)];

                    $bundle->addRecommender($user);
                    $user->addRecommendedBundle($bundle);

                    $entityManager->persist($user);
                }
            }

            $entityManager->persist($bundle);

            $this->bundles[$bundle->getName()] = $bundle;
        }

        $entityManager->flush();
    }

    /**
     * @param mixed $object
     * @param string $propertyName
     * @param mixed $value
     * @return null
     */
    private function setPrivateProperty($object, $propertyName, $value)
    {
        $reflection = new \ReflectionObject($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * Checks, that page contains specified texts in order.
     *
     * @Then /^(?:|I )should see following texts in order:$/
     */
    public function assertPageContainsTextsInOrder(TableNode $table)
    {
        $texts = array();
        foreach($table->getRows() as $row) {
            $texts[] = $row[0];
        }
        $pattern = "/".implode(".*", $texts)."/s";

        $actual = $this->getSession()->getPage()->getText();

        try {
            assertRegExp($pattern, $actual);
        } catch (AssertException $e) {
            $message = sprintf('The texts "%s" was not found in order anywhere on the current page', implode('", "', $texts));
            throw new ExpectationException($message, $this->getSession(), $e);
        }
    }

    /**
     * @Then /^(?:|I )should be able to find an element "(?P<element>[^"]*)" with following texts:$/
     */
    public function assertThereIsElementContainingTexts($element, TableNode $table)
    {
        $nodes = $this->getSession()->getPage()->findAll('css', $element);

        $texts = array();
        foreach($table->getRows() as $row) {
            $texts[] = $row[0];
        }

        if (count($nodes) == 0) {
            throw new ElementNotFoundException(
                $this->getSession(), 'element', 'css', $element
            );
        }

        foreach($nodes as $node) {
            try {
                foreach($texts as $text) {
                    assertContains($text, $node->getText());
                }
                return;
            } catch (AssertException $e) {
                 // search in next node
            }
        }

        $message = sprintf('The texts "%s" was not found in any element matching css "%s"', implode('", "', $texts), $element);
        throw new ElementTextException($message, $this->getSession(), $node);
    }

    /**
     * @Given /^I should be able to find a bundle row with following texts:$/
     */
    public function assertThereIsBundleRowWithFollowingTexts(TableNode $table)
    {
        return new Step\Then('I should be able to find an element ".bundle" with following texts:', $table);
    }

    /**
     * @Given /^I search for "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function searchFor($text)
    {
        return array(
            new Step\When('I fill in "search-query" with "'.$text.'"'),
            new Step\When('I press "search-btn"')
        );
    }

    /**
     * @Then /^I should be on "(?P<username>[^"]+)\/(?P<name>[^"]+)" bundle page$/
     */
    public function assertBundlePage($username, $name)
    {
        $url = $this->getRouter()->generate('bundle_show', array('username' => $username, 'name' => $name));

        return new Step\Then('I should be on "'.$url.'"');
    }

    /**
     * @Given /^the bundles have following keywords:$/
     */
    public function theBundlesHaveFollowingKeywords(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        foreach ($table->getHash() as $row) {
            if (isset($this->bundles[$row['bundle']])) {
                $bundle = $this->bundles[$row['bundle']];
                $keyword = $entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Keyword')->findOrCreateOne($row['keyword']);

                $bundle->addKeyword($keyword);
                $entityManager->persist($bundle);
            }
        }

        $entityManager->flush();
    }

    /**
     * @Then /^I should see "([^"]*)" atom entry$/
     */
    public function iShouldSeeAtomEntry($entryId)
    {
        return new Step\Then(sprintf('the response should contain "%s"', $entryId));
    }

    /**
     * @Then /^I should see don\'t recommend button$/
     */
    public function iShouldSeeDonTRecommendButton()
    {
        return new Step\Then('I should see "I don\'t recommend this bundle"');
    }

    /**
     * @Then /^I should see recommend button$/
     */
    public function iShouldSeeRecommendButton()
    {
        return new Step\Then('I should see "I recommend this bundle"');
    }

    /**
     * @Then /^response is successful$/
     */
    public function responseIsSuccessful()
    {
        return new Step\Then('the response status code should be 200');
    }

    /**
     * @Given /^I am at homepage$/
     */
    public function iAmAtHomepage()
    {
        return new Step\Given('I go to "/"');
    }

    /**
     * @Then /^I should see "([^"]*)" developer$/
     */
    public function iShouldSeeDeveloper($username)
    {
        return new Step\Then(sprintf('I should see "%s"', $username));
    }

    /**
     * @Then /^I should see that "([^"]*)" is managed by developer$/
     */
    public function iShouldSeeThatIsManagedByDeveloper($bundleName)
    {
        return new Step\Then(sprintf('I should see "%s" in the ".bundles-i-manage" element', $bundleName));
    }

    /**
     * @When /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        if (!$this->users[$username]) {
            throw new ExpectationException('User not found');
        }
        $user = $this->users[$username];

        $json = new \stdClass;
        $json->login = $user->getUsername();

        $githubToken = new GithubToken($json, 'access_token');
        $token = new OAuthToken($user->getRoles(), $githubToken);
        $token->setUser($user);
        $token->setAuthenticated(true);

        //it is little hackish but without it "hasPreviousSession" in Request return false and we do not be logged in
        $this->getApplicationClient()->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(session_name(), true));
        $this->getApplicationContainer()->get('session')->set('_security_oauth', serialize($token));
    }

    protected function getApplicationContainer()
    {
        return $this->getApplicationClient()->getContainer();
    }

    protected function getApplicationClient()
    {
        $driver = $this->getSession()->getDriver();

        if (!$driver instanceof SymfonyDriver) {
            throw new UnsupportedDriverActionException(
                'You need to tag the scenario with '.
                '"@mink:symfony". Using the application container is not '.
                'supported by %s', $driver
            );
        }

        return $driver->getClient();
    }

    /**
     * Returns entity manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Returns router service
     *
     * @return Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getRouter()
    {
        return $this->getContainer()->get('router');
    }
}
