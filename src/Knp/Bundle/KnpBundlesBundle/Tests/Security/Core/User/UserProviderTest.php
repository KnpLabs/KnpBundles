<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Security\Core\Developer;

use Knp\Bundle\KnpBundlesBundle\Security\Core\User\UserProvider;

class UserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDeveloperByUsername()
    {
        $john = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\Developer');

        $userManager = $this->getMock('Knp\Bundle\KnpBundlesBundle\Entity\OwnerManager', array(
            'findDeveloperBy'
        ), array(), '', false);

        $userManager->expects($this->once())
            ->method('findDeveloperBy')
            ->with($this->equalTo(array('name' => 'john')))
            ->will($this->returnValue($john));

        $provider = new UserProvider($userManager);

        $this->assertEquals($john, $provider->loadUserByUsername('john'));
    }
}
