<?php

namespace Bundle\BundleStockBundle\Tests\Bundle;

use Bundle\BundleStockBundle\Document\Bundle;
use Symfony\Framework\FoundationBundle\Test\WebTestCase;

class BundleTest extends WebTestCase
{
    /**
     * The Bundle validator instace
     *
     * @var Validator
     */
    protected $validator = null;

    public function createValidBundle()
    {
        $bundle = new Bundle();
        $bundle->setName('MarkdownBundle');
        $bundle->setAuthor('ornicar');
        return $bundle;
    }

    public function testNameValidation()
    {
        $bundle = $this->createValidBundle();

        $bundle->setName(null);
        $this->assertRegexp('/This value should not be blank/s', $this->validator->validate($bundle)->__toString());

        $bundle->setName('myNameIsNotValidAtAll');
        $this->assertRegexp('/This value is not valid/s', $this->validator->validate($bundle)->__toString());

        $bundle->setName('Bundle');
        $this->assertRegexp('/This value is not valid/s', $this->validator->validate($bundle)->__toString());
    }

    public function testAuthorValidation()
    {
        $bundle = $this->createValidBundle();

        $bundle->setAuthor(null);
        $this->assertRegexp('/This value should not be blank/s', $this->validator->validate($bundle)->__toString());

        $bundle->setAuthor('bad/author');
        $this->assertRegexp('/This value is not valid/s', $this->validator->validate($bundle)->__toString());
    }

    public function testValid()
    {
        $bundle = $this->createValidBundle();
        $this->assertEquals(new \ArrayIterator(), $this->validator->validate($bundle)->getIterator());
    }

    public function setUp()
    {
        $this->validator = $this->createClient()->getKernel()->getContainer()->getValidatorService();
    }
    
    /**
     * Get validator
     * @return Validator
     */
    public function getValidator()
    {
      return $this->validator;
    }
    
    /**
     * Set validator
     * @param  Validator
     * @return null
     */
    public function setValidator($validator)
    {
      $this->validator = $validator;
    }
    
}
