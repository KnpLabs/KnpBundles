<?php

namespace Knp\Bundle\KnpBundlesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Knp\Bundle\KnpBundlesBundle\Entity;
use Doctrine\Common\Persistence\ObjectManager;

class Data implements FixtureInterface
{
    protected $devNames = array(
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

    protected $orgNames = array(
        'KnpLabs'          => 'Happy Awesome Developers',
        'FriendsOfSymfony' => 'FriendsOfSymfony',
        'sonata-project'   => 'Sonata Project',
        'nelmio'           => 'Nelmio',
        'robertJobs'       => 'jobs Robert',
        'google'           => 'google',
        'apple'            => 'apple',
        'selenium'         => 'selenium'
    );

    private $keywords = array(
        'lorem', 'ipsum', 'dolor', 'sit', 'amet', 'consectetur', 'adipiscing', 'elit'
    );

    private $licenses = array(
        'GPL', 'New BSD', 'MIT'
    );

    private $descriptions = array(
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut a est elit, id tempus elit. Nulla facilisi.
Suspendisse tristique sagittis auctor. Donec consequat, nisl sed mollis ullamcorper, elit erat lobortis est,
non mollis est nulla sed metus. Vestibulum eleifend lacinia ullamcorper. Donec sollicitudin lorem vel ipsum
euismod malesuada. Nulla eu arcu eget nisi hendrerit hendrerit. Etiam non odio hendrerit dolor convallis luctus.
Ut fringilla pulvinar turpis, sed consectetur sem euismod nec. Integer ac urna id quam vehicula faucibus.
Fusce et erat sit amet ante dictum suscipit vel egestas sem. Vivamus ut tortor nibh. Sed volutpat erat eu sapien
ultrices vulputate. Morbi semper suscipit sodales. Ut dictum massa at erat sagittis ut dignissim massa scelerisque.
Curabitur non eleifend eros.',

        'Aliquam quam libero, condimentum ac dapibus vitae, posuere ac velit. Nulla nunc nunc, congue vel rutrum a,
ultricies a sem. Sed vehicula justo at magna bibendum at tempor metus scelerisque. Fusce nulla magna, rhoncus
a posuere at, pharetra eget sem. Donec sit amet mi sit amet mauris pretium adipiscing sit amet vitae magna.
Maecenas eleifend laoreet mauris at ultricies. Suspendisse suscipit sem sed est venenatis non dignissim mauris
suscipit. Phasellus volutpat, libero ac tincidunt laoreet, augue ligula eleifend tellus, et commodo lacus mi
ac quam. Nam a metus id lorem consectetur pharetra. Quisque et erat lectus. Vestibulum mattis vulputate nisi,
in adipiscing purus pretium ac. Mauris non metus augue. Nulla porta feugiat eros non pharetra. Suspendisse
vel augue quam, id cursus nulla.',

        'Vestibulum dui arcu, molestie a sodales non, volutpat vel nisi. Mauris in nisi id odio feugiat adipiscing at sit
amet neque. Sed rhoncus leo imperdiet diam consectetur nec imperdiet erat condimentum. Nam imperdiet, odio non
sollicitudin placerat, sapien nisi semper lacus, vitae mollis ligula augue eu magna. Ut dignissim, ligula et tempus
eleifend, ipsum risus lacinia libero, in elementum arcu turpis vel eros. Etiam metus leo, sollicitudin ac tincidunt
non, ornare a ligula. Maecenas rhoncus lorem a dui dapibus tristique ut ac tellus. Donec hendrerit condimentum erat,
quis consequat diam convallis non. Vivamus nulla erat, convallis at laoreet a, pretium quis mi.',

        'Aliquam nec lacinia lectus. Ut gravida lorem et ante faucibus ullamcorper. Nullam hendrerit ligula at erat luctus
a tristique neque dapibus. Ut auctor, sapien in cursus gravida, justo tellus tempus justo, at pharetra nisl velit
sed nisl. Suspendisse tempus urna id leo interdum mollis feugiat in augue. Aliquam neque enim, vulputate sed lacinia
sed, placerat eu augue. Fusce dictum augue a ante venenatis eget molestie felis aliquet. Fusce dictum varius dictum.',

        'Nunc venenatis vehicula semper. Curabitur quis arcu nisi, at tincidunt quam. Nam id dui vel felis sollicitudin
sollicitudin sed a dui. Phasellus consectetur ligula vitae metus laoreet pretium. Maecenas mollis tempor purus
et sollicitudin. Proin quis nisi velit, vitae hendrerit metus. Aliquam erat volutpat. Suspendisse et nisi quis
ante blandit aliquam. Phasellus mattis pulvinar adipiscing. Sed eu magna a odio mollis venenatis nec nec magna.
Aenean vulputate ante sed metus eleifend semper. Vestibulum id tincidunt eros. Praesent vel ipsum eget nisi semper
aliquam non id lorem.'
    );

    private $readme = <<<EOD
### Readme of __BUNDLE__

```
something else?
```

Code samples:

```  yaml
---
# FILE /myapp/Mapping/Entity.Role.dcm.yml
Entity\Role:
  type: entity
  table: roles
  id:
    id:
      type: integer
      generator:
        strategy: AUTO
```

- php code
- xml config

look here

```  php
<?php
include('doctrine.php');
```

and some xml

```xml
<?xml version="1.0" encoding="UTF-8" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="knp_bundles.finder.aggregate.class">Knp\Bundle\KnpBundlesBundle\Finder\Aggregate</parameter>
        <parameter key="knp_bundles.finder.google.class">Knp\Bundle\KnpBundlesBundle\Finder\Google</parameter>
        <parameter key="knp_bundles.finder.github.class">Knp\Bundle\KnpBundlesBundle\Finder\Github</parameter>
    </parameters>
</container>
```

Testing inline markup for code `single tick text`

Testing inline markup for code ``double tick text``

Testing inline markup for code ```triple tick text```

And some standard code **here**

    <?php
    class XX
    {
        //
    }

EOD;

    private $states = array(
        Entity\Bundle::STATE_UNKNOWN,
        Entity\Bundle::STATE_NOT_YET_READY,
        Entity\Bundle::STATE_READY,
        Entity\Bundle::STATE_DEPRECATED
    );

    private $canonicalConfigDump = <<<EOT
vendor_bundle_name:
    app_id:               ~ # Required
    secret:               ~ # Required
    file:                 ~
    cookie:               false
    domain:               ~
    alias:                ~
    logging:              %kernel.debug%
    culture:              en_US
    class:
        api:                  Vendor\\FixtureBundle\\APIKey
        type:                 Vendor\\FixtureBundle\\Type
    permissions:          []

EOT;

    public function load(ObjectManager $manager)
    {
        $developers = array();
        $organizations = array();

        $i = 0;
        foreach ($this->orgNames as $name => $fullName) {
            $i++;
            $organization = new Entity\Organization();
            $organization->fromArray(array(
                'name'      => $name,
                'fullName'  => $fullName,
                'email'     => strtolower(str_replace(' ', '.', $fullName)).'@foomail.bar',
                'location'  => ($i%2) ? 'Location '.$i : null,
                'url'       => ($i%2) ? 'blog'.$i.'.com' : null,
                'score'     => 0,
                'createdAt' => new \DateTime(sprintf('%d days ago', rand(1, 50)))
            ));

            $manager->persist($organization);

            $organizations[] = $organization;
        }

        $i = 0;
        foreach ($this->devNames as $name => $fullName) {
            $i++;

            $developer = new Entity\Developer();
            $developer->fromArray(array(
                'name'      => $name,
                'email'     => strtolower(str_replace(' ', '.', $fullName)).'@foomail.bar',
                'fullName'  => $fullName,
                'githubId'  => $name,
                'company'   => ($i%2) ? 'Company '.$i : null,
                'location'  => ($i%2) ? 'Location '.$i : null,
                'url'       => ($i%2) ? 'blog'.$i.'.com' : null,
                'score'     => 0,
                'createdAt' => new \DateTime(sprintf('%d days ago', rand(1, 50)))
            ));

            if (isset($organizations[$i+1])) {
                $developer->addOrganization($organizations[$i+1]);
            }

            $manager->persist($developer);

            $developers[] = $developer;
        }

        foreach ($developers as $i => $developer) {
            $contributors = array();
            $contributors[] = isset($developers[$i + 1]) ? $developers[$i + 1] : $developers[0];
            $contributors[] = isset($developers[$i - 1]) ? $developers[$i - 1] : $developers[count($developers) - 1];

            /* @var $contributor Entity\Developer */
            $contributor    = array_pop($contributors);

            $bundle = $this->makeBundle($manager, $developer, $i, $contributor);

            if ($i%5 == 0) {
                $bundle->setLastTweetedAt(new \DateTime());
            } else {
                $bundle->setVersionsHistory(
                    array(
                        'symfony' => array(
                            'dev-master' => '2.1.*',
                            '1.2.0'      => '2.0.*',
                            '1.1.0'      => '2.*',
                        ),
                        'dependencies' => array(
                            'dev-master' => array(
                                'name' => 'friendsofsymfony/user-bundle',
                                'extra' => array(
                                    'branch-alias' => array('dev-master' => '2.0.x-dev')
                                ),
                                'require' => array(
                                    'php' => '>=5.3.2',
                                    'symfony/framework-bundle' => '>=2.1,<2.3-dev',
                                    'symfony/security-bundle' => '>=2.1,<2.3-dev'
                                ),
                                'require-dev' => array(
                                    'twig/twig' => '*',
                                    'doctrine/doctrine-bundle' => '*',
                                    'swiftmailer/swiftmailer' => '*',
                                    'willdurand/propel-typehintable-behavior' => 'dev-master',
                                    'symfony/validator' => '2.1.*',
                                    'symfony/yaml' => '2.1.*'
                                ),
                                'suggest' => array(
                                    'doctrine/couchdb-odm-bundle' => '*',
                                    'doctrine/doctrine-bundle' => '*',
                                    'doctrine/mongodb-odm-bundle' => '*'
                                )
                            ),
                            '1.2.x-dev' => array(
                                'name' => 'friendsofsymfony/user-bundle',
                                'require' => array(
                                    'php' => '>=5.3.2',
                                    'symfony/framework-bundle' => '>=2.1,<2.3-dev',
                                    'symfony/security-bundle' => '>=2.1,<2.3-dev'
                                ),
                                'require-dev' => '',
                                'suggest' => ''
                            )
                        )
                    )
                );
            }
            $bundle->setScore(mt_rand(10, 666));

            $bundle->addRecommender(isset($developers[$i + 2]) ? $developers[$i + 2] : ($developers[0] != $developer ? $developers[0] : $developers[1]));
            if (isset($this->keywords[$i])) {
                $keyword = new Entity\Keyword();
                $keyword->setValue($this->keywords[$i]);

                $bundle->addKeyword($keyword);
                $manager->persist($keyword);
            }
            if (isset($this->keywords[$i+1])) {
                $keyword = new Entity\Keyword();
                $keyword->setValue($this->keywords[$i+1]);

                $bundle->addKeyword($keyword);
                $manager->persist($keyword);
            }

            $manager->persist($bundle);

            // Add some scores for bundles
            $today = new \DateTime();
            // We add a various number of scores for a given bundle
            $daysBefore    = crc32($bundle->getName().'-days') % 50;
            $maxScore      = crc32($bundle->getName()) % 50;
            $previousScore = $maxScore;

            while ($daysBefore-- > 0) {
                $date = clone $today;
                $date->sub(new \DateInterval('P'.$daysBefore.'D'));

                $score = new Entity\Score();
                $score->setBundle($bundle);
                $score->setValue($previousScore + $daysBefore);
                $score->setDate($date);

                $manager->persist($score);
                $previousScore = $score->getValue();
            }
        }

        foreach ($organizations as $organization) {
            for ($i = 1; $i < rand(2, 7); $i++) {
                $manager->persist($this->makeBundle($manager, $organization, $i));
            }
        }

        $manager->flush();
    }

    protected function makeBundle($manager, Entity\Owner $owner, $i, $contributor = null)
    {
        $trilean = array(true, false, null);

        $bundle = new Entity\Bundle();
        $bundle->fromArray(array(
            'name'          => ucfirst($owner->getName()).$i.'FooBundle',
            'description'   => $this->descriptions[mt_rand(0, 4)],
            'homepage'      => ($i%2) ? 'Bundle'.$i.'.com' : null,
            'readme'        => str_replace('__BUNDLE__', "the bundle number: {$i}", $this->readme),
            'usesTravisCi'  => ($i%2) ? false : true,
            'composerName'  => ($i%2) ? null : 'knplabs/knp-menu-bundle',
            'versionsHistory' => array(
                'symfony' => array(
                    'dev-master' => '2.1.*',
                    '1.2.0'      => '2.0.*',
                    '1.1.0'      => '2.*',
                ),
                'dependencies' => array(
                    'dev-master' => array(
                        'name' => 'friendsofsymfony/user-bundle',
                        'extra' => array(
                            'branch-alias' => array('dev-master' => '2.0.x-dev')
                        ),
                        'require' => array(
                            'php' => '>=5.3.2',
                            'symfony/framework-bundle' => '>=2.1,<2.3-dev',
                            'symfony/security-bundle' => '>=2.1,<2.3-dev'
                        ),
                        'require-dev' => array(
                            'twig/twig' => '*',
                            'doctrine/doctrine-bundle' => '*',
                            'swiftmailer/swiftmailer' => '*',
                            'willdurand/propel-typehintable-behavior' => 'dev-master',
                            'symfony/validator' => '2.1.*',
                            'symfony/yaml' => '2.1.*'
                        ),
                        'suggest' => array(
                            'doctrine/couchdb-odm-bundle' => '*',
                            'doctrine/doctrine-bundle' => '*',
                            'doctrine/mongodb-odm-bundle' => '*'
                        )
                    ),
                    '1.2.x-dev' => array(
                        'name' => 'friendsofsymfony/user-bundle',
                        'require' => array(
                            'php' => '>=5.3.2',
                            'symfony/framework-bundle' => '>=2.1,<2.3-dev',
                            'symfony/security-bundle' => '>=2.1,<2.3-dev'
                        ),
                        'require-dev' => '',
                        'suggest' => ''
                    )
                )
            ),
            'state'         => $this->states[mt_rand(0, 3)],
            'license'       => ($i%4 == 0) ? 'Some pseudo license data' : null,
            'licenseType'   => ($i%3 == 0) ? $this->licenses[mt_rand(0, 2)] : null,
            'travisCiBuildStatus'  => ($i%2 == 0) ? $trilean[$i%3] : null,
            'nbFollowers'   => $i*10,
            'nbForks'       => $i,
            'isFork'        => false,
            'contributors'  => $contributor ? array($contributor) : array(),
            'canonicalConfig' => ($i%2 == 0) ? $this->canonicalConfigDump : null,
            'nbRecommenders' => rand(0, 90),
        ));

        $commits = array(
            array(
                'date'      => '2010-05-16T09:58:32-09:00',
                'author'    => $owner instanceof Entity\Developer ? $owner->getName() : 'Random Person',
                'message'   => 'Fix something on this Bundle',
            ),
            array(
                'date'      => '2010-05-16T09:58:32-07:00',
                'author'    => 'Fake Name',
                'message'   => 'Commit something on this bundle',
            ),
        );

        foreach ($commits as $commit) {
            $lastCommitAt = new \DateTime();
            $lastCommitAt->setTimestamp(strtotime($commit['date']));

            $bundle->setLastCommitAt($lastCommitAt);

            $activity = new Entity\Activity();
            $activity->setType(Entity\Activity::ACTIVITY_TYPE_COMMIT);
            $activity->setMessage(strtok($commit['message'], "\n\r"));
            $activity->setCreatedAt($lastCommitAt);
            $activity->setBundle($bundle);

            if ($owner->getName() == $commit['author']) {
                $owner->setLastCommitAt($lastCommitAt);
                $activity->setDeveloper($owner);
                $activity->setBundleOwnerName(strtolower($owner->getName()));
            } else {
                $activity->setAuthor($commit['author']);
                $activity->setBundleOwnerName(strtolower($commit['author']));
            }

            $manager->persist($activity);
        }

        $owner->addBundle($bundle);

        return $bundle;
    }
}
