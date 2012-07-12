<?php

namespace Knp\Bundle\KnpBundlesBundle\Features\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Buzz\Message\Request;
use Buzz\Browser;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Provides API description definitions.
 *
 * @author Luis Cordova <cordoval@gmail.com>
 */
class ApiContext extends RawMinkContext
{
    private $browser;
    private $baseUrl;
    private $authorization;
    private $placeHolders = array();
    private $headers = array();

    /**
     * Adds Basic Authentication header to next request.
     *
     * @param string $username
     * @param string $password
     *
     * @Given /^I am authenticating as "([^"]*)" with "([^"]*)" password$/
     */
    public function iAmAuthenticatingAs($username, $password)
    {
        $this->authorization = base64_encode($username.':'.$password);
        $this->addHeader('Authorization: Basic '.$this->authorization);
    }

    /**
     * Sets a HTTP Header.
     *
     * @param string $name  header name
     * @param string $value header value
     *
     * @Given /^I set header "([^"]*)" with value "([^"]*)"$/
     */
    public function iSetHeaderWithValue($name, $value)
    {
        $this->addHeader($name.': '.$value);
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method request method
     * @param string $url    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $url)
    {
        $url = $this->getBaseUrl().'/'.ltrim($this->replacePlaceHolder($url), '/');

        $this->getBrowser()->call($url, $method, $this->getHeaders());
    }

    /**
     * Sends HTTP request to specific URL with field values from Table.
     *
     * @param string    $method request method
     * @param string    $url    relative url
     * @param TableNode $post   table of post values
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with values:$/
     */
    public function iSendARequestWithValues($method, $url, TableNode $post)
    {
        $url    = $this->getBaseUrl().'/'.ltrim($this->replacePlaceHolder($url), '/');
        $fields = array();

        foreach ($post->getRowsHash() as $key => $val) {
            $fields[$key] = $this->replacePlaceHolder($val);
        }

        $this->getBrowser()->submit($url, $fields, $method, $this->getHeaders());
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $string request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody($method, $url, PyStringNode $string)
    {
        $url    = $this->getBaseUrl().'/'.ltrim($this->replacePlaceHolder($url), '/');
        $string = $this->replacePlaceHolder(trim($string));

        $this->getBrowser()->call($url, $method, $this->getHeaders(), $string);
    }

    /**
     * Sends HTTP request to specific URL with form data from PyString.
     *
     * @param string       $method request method
     * @param string       $url    relative url
     * @param PyStringNode $string request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with form data:$/
     */
    public function iSendARequestWithFormData($method, $url, PyStringNode $string)
    {
        $url    = $this->getBaseUrl().'/'.ltrim($this->replacePlaceHolder($url), '/');
        $string = $this->replacePlaceHolder(trim($string));

        parse_str(implode('&', explode("\n", $string)), $fields);

        $this->getBrowser()->submit($url, $fields, $method, $this->getHeaders());
    }

    /**
     * Checks that response has specific status code.
     *
     * @param string $code status code
     *
     * @Then /^(?:the )?response code should be (\d+)$/
     */
    public function theResponseCodeShouldBe($code)
    {
        assertSame(intval($code), $this->getBrowser()->getLastResponse()->getStatusCode());
    }

    /**
     * Checks that response body contains JSON from PyString.
     *
     * @param PyStringNode $jsonString
     *
     * @Then /^(?:the )?response should contain json:$/
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode($this->getBrowser()->getLastResponse()->getContent(), true);

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
     * Prints last response body.
     *
     * @Then print response
     */
    public function printResponse()
    {
        $request  = $this->getBrowser()->getLastRequest();
        $response = $this->getBrowser()->getLastResponse();

        $this->printDebug(sprintf("%s %s => %d:\n%s",
            $request->getMethod(),
            $request->getUrl(),
            $response->getStatusCode(),
            $response->getContent()
        ));
    }

    /**
     * @Then /^the json response should contain the following items:$/
     */
    public function theResponseShouldContainJsonItemsAsFollow(TableNode $table)
    {
        $response = $this->getBrowser()->getLastResponse()->getContent();
        var_export($response);
        $jsonResponse = json_decode($response, true);

        foreach ($table->getHash() as $row) {
            $rowKey = $row['key'];
            $rowValue = $row['value'];
            $count = 0;
            foreach ($jsonResponse as $item) {
                foreach ($item as $key => $value) {
                    if ($key == $rowKey && $value == $rowValue) {
                        $count++;
                    }
                }
            }
            assertEquals($row['count'], $count);
        }
    }

    /**
     * Returns browser instance.
     *
     * @return Browser
     */
    public function getBrowser()
    {
        if (null === $this->browser) {
            $this->browser = $this->getMinkParameter('browser') !== null ? $this->getMinkParameter('browser') : new Browser();
        }
        
        return $this->browser;
    }

    /**
     * Returns base_url string
     * 
     * @return mixed
     */
    public function getBaseUrl()
    {
        $baseUrl = $this->getMinkParameter('base_url');
        $this->baseUrl = rtrim($baseUrl, '/');
        
        return $this->baseUrl;
    }

    /**
     * Sets place holder for replacement.
     *
     * you can specify placeholders, which will
     * be replaced in URL, request or response body.
     *
     * @param string $key   token name
     * @param string $value replace value
     */
    public function setPlaceHolder($key, $value)
    {
        $this->placeHolders[$key] = $value;
    }

    /**
     * Replaces placeholders in provided text.
     *
     * @param string $string
     *
     * @return string
     */
    public function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
    }

    /**
     * Returns headers, that will be used to send requests.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Adds header
     *
     * @param string $header
     */
    protected function addHeader($header)
    {
        $this->headers[] = $header;
    }
}
