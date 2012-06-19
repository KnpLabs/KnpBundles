<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Commands;

require_once __DIR__.'../../../../../../../app/AppKernel.php';

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Imagine\Exception\RuntimeException;
use Knp\Bundle\KnpBundlesBundle\Command\KbGenerateBadgesCommand;

class KbGenerateBadgesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!function_exists('gd_info')) {
            $this->markTestSkipped();
        }
        $info = gd_info();

        if (!$info['FreeType Support']) {
            $this->markTestSkipped();
        }
    }

    public function testBadgeGenerator()
    {
        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new KbGenerateBadgesCommand());

        try {
            $command = $application->find('kb:generate:badges');
            $commandTester = new CommandTester($command);
            $commandTester->execute(array('command' => $command->getName()));

            $this->assertRegExp('/generated/', $commandTester->getDisplay());
        } catch (RuntimeException $e) {
            if ('GD is not compiled with FreeType support' == $e->getMessage()) {
                $this->markTestSkipped('GD is not compiled with FreeType support');
            } else {
                throw $e;
            }
        }
    }
}
