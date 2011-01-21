<?php

namespace Application\S2bBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Application\S2bBundle\Entity;

class Data implements FixtureInterface
{
    public function load($manager)
    {
        $nbUsers = 15;
        for($it=1; $it<=$nbUsers; $it++) {
            $user = new Entity\User();
            $user->fromArray(array(
                'name'      => 'Developer '.$it,
                'email'     => 'dev'.$it.'@mail.org',
                'fullName'  => 'Number '.$it,
                'company'   => ($it%2) ? 'Company '.$it : null,
                'location'  => ($it%2) ? 'Location '.$it : null,
                'blog'      => ($it%2) ? 'blog'.$it.'.com' : null,
                'score'     => 0
            ));

            $manager->persist($user);

            foreach(array('Bundle', 'Project') as $repoClass) {
                $fullClassName = 'Application\\S2bBundle\\Entity\\'.$repoClass;
                $repo = new $fullClassName();
                $repo->fromArray(array(
                    'name'          => 'User'.$it.$repoClass,
                    'username'      => $user->getName(),
                    'user'          => $user,
                    'description'   => 'Description of my '.$repoClass,
                    'homepage'      => ($it%2) ? $repoClass.$it.'.com' : null,
                    'readme'        => str_repeat("README of the ".$repoClass." number ".$it."\n", 20),
                    'tags'          => ($it%2) ? array('1.0', '1.1') : array(),
                    'nbFollowers'   => $it*10,
                    'nbForks'       => $it,
                    'lastCommitAt'  => new \DateTime('-'.($it*4).' day'),
                    'lastCommits'   => array(array(
                        'author'            => $user->getFullName(),
                        'login'             => $user->getName(),
                        'email'             => $user->getEmail(),
                        'committed_date'    => '2010-05-16T09:58:32-07:00',
                        'authored_date'     => '2010-05-16T09:58:32-07:00',
                        'message'           => 'Commit something on this '.$repoClass,
                        'url'               => 'http://github.com'
                    )),
                    'isFork'        => false
                ));

                $manager->persist($repo);
            }

            $manager->persist($user);
        }
    }
}
