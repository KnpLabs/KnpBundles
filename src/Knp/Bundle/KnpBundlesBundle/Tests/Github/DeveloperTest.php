<?php

namespace Knp\Bundle\KnpBundlesBundle\Tests\Github;

use Knp\Bundle\KnpBundlesBundle\Github\Developer as GithubDeveloper;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer as DeveloperEntity;

class DeveloperTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdate()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $data = array(
            'email' => 'hello@knplabs.com',
            'avatar_url' => 'hash',
            'name' => 'lorem',
            'fullname' => 'Edgar Knp',
            'company' => 'KnpLabs',
            'location' => 'Nantes',
            'blog' => 'http://knplabs.com',
        );

        $github = $this->getMock('Github\Client', array('api'));

        $githubDeveloperApi = $this->getMock('Github\Api\User', array('show'), array($github));
        $githubDeveloperApi->expects($this->any())
            ->method('show')
            ->with($this->equalTo('lorem'))
            ->will($this->returnValue($data));

        $github->expects($this->any())
            ->method('api')
            ->with('user')
            ->will($this->returnValue($githubDeveloperApi));

        $userEntity = new DeveloperEntity();
        $userEntity->setName('lorem');
        $userEntity->setFullName('lorem');

        $githubDeveloper = new GithubDeveloper($github, $output);
        $githubDeveloper->update($userEntity);

        $this->assertEquals($data['email'], $userEntity->getEmail());
        $this->assertEquals($data['name'], $userEntity->getName());
        $this->assertEquals($data['fullname'], $userEntity->getFullName());
        $this->assertEquals($data['avatar_url'], $userEntity->getAvatarUrl());
        $this->assertEquals($data['company'], $userEntity->getCompany());
        $this->assertEquals($data['location'], $userEntity->getLocation());
        $this->assertEquals($data['blog'], $userEntity->getUrl());
    }

    public function testUpdateBadUrl()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $data = array(
            'blog' => 'knplabs.com',
        );

        $github = $this->getMock('Github\Client', array('api'));

        $githubDeveloperApi = $this->getMock('Github\Api\User', array('show'), array($github));
        $githubDeveloperApi->expects($this->any())
            ->method('show')
            ->with($this->equalTo('lorem'))
            ->will($this->returnValue($data));

        $github->expects($this->any())
            ->method('api')
            ->with('user')
            ->will($this->returnValue($githubDeveloperApi));

        $userEntity = new DeveloperEntity();
        $userEntity->setName('lorem');

        $githubDeveloper = new GithubDeveloper($github, $output);
        $githubDeveloper->update($userEntity);

        $this->assertEquals('http://knplabs.com', $userEntity->getUrl());
    }

}
