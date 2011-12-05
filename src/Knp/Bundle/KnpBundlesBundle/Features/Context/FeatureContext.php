<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Event\ScenarioEvent;

use Behat\Mink\Exception\ElementNotFoundException,
    Behat\Mink\Exception\ExpectationException,
    Behat\Mink\Exception\ResponseTextException,
    Behat\Mink\Exception\ElementHtmlException,
    Behat\Mink\Exception\ElementTextException;

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
     * @param Behat\Behat\Event\ScenarioEvent $event
     *
     * @BeforeScenario
     *
     * @return null
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        $this->generateSchema();
    }

    /**
     * @param Behat\Behat\Event\ScenarioEvent $event
     *
     * @AfterScenario
     *
     * @return null
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->getEntityManager()->clear();
    }

    /**
    * @return null
    */
    private function generateSchema()
    {
        $entityManager = $this->getEntityManager();
        $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadatas)) {
            $tool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
            $tool->dropSchema($metadatas);
            $tool->createSchema($metadatas);
        } else {
            throw new Doctrine\DBAL\Schema\SchemaException('No Metadata Classes to process.');
        }
    }    
    
    /**
     * @Given /^the site has following users:$/
     */
    public function theSiteHasFollowingUsers(TableNode $table)
    {
        $container = $this->getKernel()->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

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
        $container = $this->getKernel()->getContainer();
        $entityManager = $container->get('doctrine.orm.entity_manager');

        foreach ($table->getHash() as $row) {
            $user = $this->users[$row['user_name']];
            
            $bundle = new Entity\Bundle();
            $bundle->fromArray(array(
                'name'          => $row['name'],
                'user'          => $user,
                'username'      => $user->getName(),
                'description'   => $row['description'],
                'lastCommitAt'  => new \DateTime($row['lastCommitAt']),
            ));

            $bundle->setScore($row['score']);
            $this->setPrivateProperty($bundle, "trend1", $row['trend1']);
            
            $entityManager->persist($bundle);
        }

        $entityManager->flush();
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
            foreach($texts as $text) {
                try {
                    assertContains($text, $node->getText());
                } catch (AssertException $e) {
                    continue 2; // search in next node
                }
            }

            return;
        }

        $message = sprintf('The texts "%s" was not found in any element matching css "%s"', implode('", "', $texts), $element);
        throw new ElementTextException($message, $this->getSession(), $node);
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
