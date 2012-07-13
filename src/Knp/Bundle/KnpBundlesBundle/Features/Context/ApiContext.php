<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\Behat\Context\Step;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;

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

        assertCount((int) count($table->getHash()), $jsonResponse);

        $expectedItems = array();
        foreach ($table->getHash() as $row) {
            foreach ($row as $key => $element) {
                $expectedItems[$key][] = $element;
            }
        }

        foreach ($jsonResponse as $element) {
            foreach ($expectedItems as $key => $items) {
                assertContains($element[$key], $items);
            }
        }
    }
}
