<?php

namespace Knp\Bundle\KnpBundlesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Knp\Bundle\KnpBundlesBundle\Entity;

class Data implements FixtureInterface
{
    protected $names = array(
        'John'      => 'John Doe',
        'Brian'     => 'Brian Lester',
        'Jack'      => 'Jack Gill',
        'Olivia'    => 'Olivia Pace',
        'Nola'      => 'Nola Weaver',
        'Oren'      => 'Oren Tyler',
        'Warren'    => 'Warren Spencer',
        'Jacob'     => 'Jacob Gallegos',
        'Jordan'    => 'Jordan Saunders',
        'Xavier'    => 'Xavier Stein',
        'Beck'      => 'Beck Nash',
        'Ann'       => 'Ann Perry',
        'Chase'     => 'Chase Hoffman',
        'Greg'      => 'Gregory Joyner',
        'Dexter'    => 'Dexter Schwartz'
    );

    public function load($manager)
    {
        $users = array();
        $trilean = array(true, false, null);

        $i = 0;
        foreach ($this->names as $name => $fullName) {
            $i++;

            $user = new Entity\User();
            $user->fromArray(array(
                'name'      => $name,
                'email'     => strtolower(str_replace(' ', '.', $fullName)).'@foomail.bar',
                'fullName'  => $fullName,
                'company'   => ($i%2) ? 'Company '.$i : null,
                'location'  => ($i%2) ? 'Location '.$i : null,
                'blog'      => ($i%2) ? 'blog'.$i.'.com' : null,
                'score'     => 0
            ));

            $manager->persist($user);

            $users[] = $user;
        }

        foreach ($users as $i => $user) {

            $contributors = array();
            $contributors[] = isset($users[$i + 1]) ? $users[$i + 1] : $users[0];
            $contributors[] = isset($users[$i - 1]) ? $users[$i - 1] : $users[count($users) - 1];

            foreach(array('Bundle', 'Project') as $repoClass) {

                $contributor    = array_pop($contributors);
                $fullClassName  = 'Knp\\Bundle\\KnpBundlesBundle\\Entity\\'.$repoClass;

                $repo = new $fullClassName();
                $repo->fromArray(array(
                    'name'          => ucfirst($user->getName()).'Foo'.$repoClass,
                    'username'      => $user->getName(),
                    'user'          => $user,
                    'description'   => 'Description of my '.$repoClass,
                    'homepage'      => ($i%2) ? $repoClass.$i.'.com' : null,
                    'readme'        => str_repeat("README of the ".$repoClass." number ".$i."\n", 20),
                    'tags'          => ($i%2) ? array('1.0', '1.1') : array(),
                    'usesTravisCi'  => ($i%2) ? false : true,
                    'travisCiBuildStatus'  => ($i%2 == 0) ? $trilean[$i%3] : null,
                    'nbFollowers'   => $i*10,
                    'nbForks'       => $i,
                    'lastCommitAt'  => new \DateTime('-'.($i*4).' day'),
                    'lastCommits'   => array(
                        array(
                            'author'            => array(
                                'name'  => $contributor->getFullName(),
                                'login' => $contributor->getName(),
                                'email' => $contributor->getEmail()
                            ),
                            'url'               => 'http://github.com',
                            'committed_date'    => '2010-05-16T09:58:32-09:00',
                            'authored_date'     => '2010-05-16T09:58:32-09:00',
                            'message'           => 'Fix something on this '.$repoClass,
                        ),
                        array(
                            'author'            => array(
                                'name'  => $user->getFullName(),
                                'login' => $user->getName(),
                                'email' => $user->getEmail()
                            ),
                            'url'               => 'http://github.com',
                            'committed_date'    => '2010-05-16T09:58:32-07:00',
                            'authored_date'     => '2010-05-16T09:58:32-07:00',
                            'message'           => 'Commit something on this '.$repoClass,
                        ),
                    ),
                    'isFork'        => false,
                    'contributors'  => array($contributor)
                ));

                $manager->persist($repo);

                // Add some scores for projects
                $today = new \DateTime();
                // We add a various number of scores for a given project/bundle
                $daysBefore = crc32($repo->getName().'-days') % 50;
                $maxScore = crc32($repo->getName()) % 50;
                $previousScore = $maxScore;

                while($daysBefore-- > 0) {
                    $date = clone $today;
                    $date->sub(new \DateInterval('P'.$daysBefore.'D'));

                    $score = new Entity\Score();
                    $score->setRepo($repo);
                    $score->setValue($previousScore + $daysBefore);
                    $score->setDate($date);

                    $manager->persist($score);
                    $previousScore = $score->getValue();
                }
            }
        }

        $manager->flush();
    }
}
