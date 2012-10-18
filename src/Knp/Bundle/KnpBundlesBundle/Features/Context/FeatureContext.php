<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

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

use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\CommonContexts\SymfonyDoctrineContext;

use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

use PHPUnit_Framework_ExpectationFailedException as AssertException;
use Knp\Bundle\KnpBundlesBundle\Entity;

/**
 * Feature context.
 *
 * @author Luis Cordova <cordoval@gmail.com>
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class FeatureContext extends RawMinkContext implements KernelAwareInterface
{
    private $developers;
    private $organizations;
    private $bundles;
    private $placeHolders = array();

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel;

    public function __construct($parameters)
    {
        $this->useContext('symfony_doctrine', new SymfonyDoctrineContext());
        $this->useContext('solr', new SolrContext());
        $this->useContext('mink', new MinkContext());

        $this->setPlaceHolder('%base_url%', rtrim($parameters['base_url'], '/app_test.php'));
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
     * @Then /^(?:the )?response should contain json:$/
     */
    public function assertResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->getSession()->getPage()->getContent(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n".$this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        assertCount(count($etalon), $actual);
        foreach ($actual as $needle) {
            assertContains($needle, $etalon);
        }
    }

    /**
     * @Then /^I should be on "(?P<ownerName>[^"]+)\/(?P<name>[^"]+)" bundle page$/
     */
    public function assertBundlePage($ownerName, $name)
    {
        $url = $this->getRouter()->generate('bundle_show', array('ownerName' => $ownerName, 'name' => $name));

        return new Step\Then('I should be on "'.$url.'"');
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
     * @Then /^I should see "([^"]*)" atom entry$/
     */
    public function iShouldSeeAtomEntry($entryId)
    {
        return new Step\Then(sprintf('the response should contain "%s"', $entryId));
    }

    /**
     * @Then /^I should not see recommend button$/
     */
    public function iShouldNotSeeRecommendButton()
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
     * @Then /^I should see "([^"]*)" (developer|organization)$/
     */
    public function iShouldSeeOwner($ownerName)
    {
        return new Step\Then(sprintf('I should see "%s"', $ownerName));
    }

    /**
     * @Then /^I should see that "([^"]*)" is managed by (developer|organization)$/
     */
    public function iShouldSeeThatIsManagedByDeveloper($bundleName)
    {
        return new Step\Then(sprintf('I should see "%s" in the "#owned" element', $bundleName));
    }

    /**
     * @When /^(?:I )?send a GET request to "([^"]+)"$/
     */
    public function iSendARequest($url)
    {
        return new Step\When(sprintf('I go to "%s"', ltrim($url, '/')));
    }

    /**
     * @When /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        if (!$this->developers[$username]) {
            throw new ExpectationException('User not found');
        }
        $user = $this->developers[$username];

        $token = new OAuthToken(null,$user->getRoles());
        $token->setUser($user);
        $token->setAuthenticated(true);

        $session = $this->getContainer()->get('session');
        $session->set('_security_secured_area', serialize($token));
        $session->save();
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
     * @Given /^the site has following users:$/
     */
    public function theSiteHasFollowingUsers(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        $this->developers = array();
        foreach ($table->getHash() as $row) {
            $developer = new Entity\Developer();
            $developer->fromArray(array(
                 'name'  => $row['name'],
                 'score' => isset($row['score']) ? $row['score'] : 0,
            ));

            if (isset($row['organization'])) {
                $organization = $this->organizations[$row['organization']];
                $developer->addOrganization($organization);
            }

            $entityManager->persist($developer);

            $this->developers[$developer->getName()] = $developer;
        }

        $entityManager->flush();
    }

    /**
     * @Given /^the site has following organizations:$/
     */
    public function theSiteHasFollowingOrganizations(TableNode $table)
    {
        $entityManager = $this->getEntityManager();

        $this->organizations = array();
        foreach ($table->getHash() as $row) {
            $organization = new Entity\Organization();
            $organization->fromArray(array(
                'name'  => $row['name'],
                'score' => isset($row['score']) ? $row['score'] : 0,
            ));

            $entityManager->persist($organization);

            $this->organizations[$organization->getName()] = $organization;
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
            if (isset($this->developers[$row['username']])) {
                $owner = $this->developers[$row['username']];
            } elseif (isset($this->organizations[$row['username']])) {
                $owner = $this->organizations[$row['username']];
            } else {
                continue;
            }

            $bundle = new Entity\Bundle();
            $bundle->fromArray(array(
                'name'          => $row['name'],
                'owner'         => $owner,
                'ownerName'     => $owner->getName(),
                'description'   => $row['description'],
                'state'         => isset($row['state']) ? $row['state'] : Entity\Bundle::STATE_UNKNOWN,
            ));

            if (isset($row['license'])) {
                $bundle->setLicenseType($row['license']);
            }
            if (isset($row['createdAt'])) {
                $bundle->setCreatedAt(new \DateTime($row['createdAt']));
            }
            if (isset($row['lastCommitAt'])) {
                $bundle->setLastCommitAt(new \DateTime($row['lastCommitAt']));
            }

            $bundle->setScore($row['score']);

            $this->setPrivateProperty($bundle, "trend1", $row['trend1']);

            if (isset($row['recommendedBy'])) {
                $ownerNames = explode(',', $row['recommendedBy']);
                foreach ($ownerNames as $ownerName) {
                    $owner = $this->developers[trim($ownerName)];

                    $bundle->addRecommender($owner);
                    $owner->addRecommendedBundle($bundle);

                    $entityManager->persist($owner);
                }
            }

            $entityManager->persist($bundle);

            $this->bundles[$bundle->getName()] = $bundle;
        }

        $entityManager->flush();
    }

    /**
     * @Given /^organization "([^"]*)" has following members:$/
     */
    public function organizationHasFollowingMembers($orgName, TableNode $table)
    {
        throw new PendingException();
    }

    /**
     * Sets Kernel instance.
     *
     * @param KernelInterface $kernel HttpKernel instance
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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

    /**
     * gets container from kernel
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->kernel->getContainer();
    }

    /**
     * @param string $key
     * @param string $value
     */
    protected function setPlaceHolder($key, $value)
    {
        $this->placeHolders[$key] = $value;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
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
}
