<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Security\Core\Developer;

use Knp\Bundle\KnpBundlesBundle\Security\Core\User\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDeveloperByDevelopername()
    {
        $john = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Developer');
        
        $userManager = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\OwnerManager', array(
            'getOrCreate'
        ), array(), '', false);

        $userManager->expects($this->once())
            ->method('getOrCreate')
            ->with($this->equalTo('john'))
            ->will($this->returnValue($john));

        $provider = new UserProvider($userManager);

        $this->assertEquals($john, $provider->loadUserByUsername('john'));
    }
}
