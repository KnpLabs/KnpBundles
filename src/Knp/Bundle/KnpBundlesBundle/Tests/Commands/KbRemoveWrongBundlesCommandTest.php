<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Commands;

require_once __DIR__.'../../../../../../../app/AppKernel.php';

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Imagine\Exception\RuntimeException;
use Knp\Bundle\KnpBundlesBundle\Command\KbRemoveWrongBundlesCommand;

class KbRemoveWrongBundlesCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testRemovingOneInvalidBundleFromDatabase()
    {
        $this->markAsIncomplete('rather trying to test updater service');

        $kernel = new \AppKernel('test', true);
        $kernel->boot();

        $application = new Application($kernel);
        $application->add(new KbRemoveWrongBundlesCommand());

        $command = $application->find('kb:remove:wrong-bundles');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/generated/', $commandTester->getDisplay());
    }
}
