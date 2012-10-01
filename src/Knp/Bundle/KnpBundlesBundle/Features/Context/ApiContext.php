<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\Behat\Context\Step;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Provides knpbundles API steps
 *
 * @author Luis Cordova <cordoval@gmail.com>
 * @author Leszek Prabucki <leszek.prabucki@gmail.com>
 */
class ApiContext extends RawMinkContext
{
    /**
     * @When /^I send a GET request to "([^"]*)"$/
     */
    public function iSendAGetRequestTo($routePath)
    {
        return new Step\Then(sprintf('I am on "%s"', $routePath));
    }

    /**
     * @Then /^I should get JSON with following items:$/
     */
    public function iShouldGetJsonWithFollowingItems(TableNode $table)
    {
        $response = $this->getSession()->getPage()->getContent();

        $jsonResponse = json_decode($response, true);

        assertTrue(isset($jsonResponse['results'], $jsonResponse['total']));
        assertCount(count($table->getHash()), $jsonResponse['results']);

        $expectedItems = array();
        foreach ($table->getHash() as $row) {
            foreach ($row as $key => $element) {
                $expectedItems[$key][] = $element;
            }
        }

        foreach ($jsonResponse['results'] as $element) {
            foreach ($expectedItems as $key => $items) {
                assertContains($element[$key], $items);
            }
        }
    }

    /**
     * @Then /^(?:the )?response code should be (\d+)$/
     */
    public function theResponseStatusShouldBe($code)
    {
        assertSame(intval($code), $this->getSession()->getStatusCode());
    }

    /**
     * @Then /^(?:the )?response should equal to JSON:$/
     */
    public function theResponseShouldEqualToJson(PyStringNode $jsonString)
    {
        $expected = json_decode($jsonString->getRaw(), true);
        $actual   = json_decode($this->getSession()->getPage()->getContent(), true);

        if (null === $expected) {
            throw new \RuntimeException("Expected data is not valid JSON:\n".$jsonString->getRaw());
        }

        assertEquals($expected, $actual, "Failed asserting expected data:\n".print_r($expected, true)."\n\nIs equal to:\n".print_r($actual, true));
    }
}
