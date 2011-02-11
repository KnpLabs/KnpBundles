<?php

namespace Knplabs\Symfony2BundlesBundle;

use PHPUnit_Framework_TestCase;

class RepoTest extends PHPUnit_Framework_TestCase
{
    public function testHasNoSubmodules()
    {
        $this->assertFalse($this->getDetector('repo1')->isProject());
    }

    public function testHasNoSymfonySubmodule()
    {
        $this->assertFalse($this->getDetector('repo2')->isProject());
    }

    public function testHasSymfonySubmodule()
    {
        $this->assertTrue($this->getDetector('repo3')->isProject());
    }

    public function testHasEmptyKernel()
    {
        $this->assertFalse($this->getDetector('repo4')->isProject());
    }

    public function testHasKernel()
    {
        $this->assertTrue($this->getDetector('repo5')->isProject());
    }

    public function testHasTwoKernels()
    {
        $this->assertTrue($this->getDetector('repo6')->isProject());
    }

    private function getDetector($fixtureName)
    {
        return new Symfony2Detector($this->getFixture($fixtureName));
    }

    private function getFixture($name)
    {
        return realpath(__DIR__.'/fixtures/'.$name);
    }
}
