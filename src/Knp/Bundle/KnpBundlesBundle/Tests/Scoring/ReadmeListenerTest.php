<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Scoring;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Scoring\ReadmeListener;

class ReadmeListenerTest extends \PHPUnit_Framework_TestCase
{
    
    public function testCalculatedScore()
    {
        $bundleWithShortReadme = new Bundle();
        $bundleWithShortReadme->setReadme('This is a very short (and quite useless) readme');

        $bundle = new Bundle();
        $bundle->setReadme(<<<README
# KnpBundles

Open-source code of the [knpbundles.com](http://knpbundles.com)
website, written in Symfony2.

Any ideas are welcome!

[![Build Status](https://secure.travis-ci.org/KnpLabs/KnpBundles.png)](http://travis-ci.org/KnpLabs/KnpBundles)

Please note that this service was previously called Symfony2Bundles but we had
to change the name due to [trademark issues](http://knplabs.com/blog/symfony2bundles-becomes-knpbundle).
README
);

        $tester = new ReadmeListener();
        $tester->updateScore($bundleWithShortReadme);
        $bundleWithShortReadme->recalculateScore();

        $tester->updateScore($bundle);
        $bundle->recalculateScore();
        
        $this->assertEquals(0, $bundleWithShortReadme->getScore());
        $this->assertEquals(5, $bundle->getScore());
    }
     
}