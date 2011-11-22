<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Github;

use Knp\Bundle\KnpBundlesBundle\Github\User as GithubUser;
use Knp\Bundle\KnpBundlesBundle\Entity\User as UserEntity;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdate()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $data = array(
            'email' => 'hello@knplabs.com',
            'gravatar_id' => 'hash',
            'name' => 'Edgar Knp',
            'company' => 'KnpLabs',
            'location' => 'Nantes',
            'blog' => 'http://knplabs.com',
        );

        $github = $this->getMock('Github_Client', array('getUserApi'));

        $githubUserApi = $this->getMock('Github_Api_User', array('show'), array($github));
        $githubUserApi->expects($this->any())
            ->method('show')
            ->with($this->equalTo('lorem'))
            ->will($this->returnValue($data));

        $github->expects($this->any())
            ->method('getUserApi')
            ->will($this->returnValue($githubUserApi));

        $userEntity = new UserEntity;
        $userEntity->setName('lorem');

        $githubUser = new GithubUser($github, $output);
        $ret = $githubUser->update($userEntity);

        $this->assertEquals($data['email'], $userEntity->getEmail());
        $this->assertEquals($data['name'], $userEntity->getFullName());
        $this->assertEquals($data['gravatar_id'], $userEntity->getGravatarHash());
        $this->assertEquals($data['company'], $userEntity->getCompany());
        $this->assertEquals($data['location'], $userEntity->getLocation());
        $this->assertEquals($data['blog'], $userEntity->getBlog());
    }

}
