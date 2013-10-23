<?php

namespace Knp\Bundle\KnpBundlesBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\NullOutput;

use Github\Client;

use Knp\Bundle\KnpBundlesBundle\Entity\Activity;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;
use Knp\Bundle\KnpBundlesBundle\Entity\Developer;
use Knp\Bundle\KnpBundlesBundle\Github\Repo;

/**
 * Manages bundle entities
 *
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class BundleManager
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var Repo
     */
    private $repoApi;

    /**
     * @param ObjectManager $entityManager
     * @param OwnerManager  $ownerManager
     * @param Repo          $repoApi
     */
    public function __construct(ObjectManager $entityManager, OwnerManager $ownerManager, Repo $repoApi)
    {
        $this->entityManager = $entityManager;
        $this->ownerManager  = $ownerManager;
        $this->repoApi       = $repoApi;
    }

    /**
     * @param array $data
     *
     * @return null|Bundle
     */
    public function findBundleBy(array $data)
    {
        return $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Bundle')->findOneBy($data);
    }

    /**
     * @param Bundle    $bundle
     * @param Developer $developer
     */
    public function manageBundleRecommendation(Bundle $bundle, Developer $developer)
    {
        if ($developer->isUsingBundle($bundle)) {
            $activity = $this->entityManager->getRepository('Knp\Bundle\KnpBundlesBundle\Entity\Activity')
                ->findOneBy(array(
                    'type'      => Activity::ACTIVITY_TYPE_RECOMMEND,
                    'bundle'    => $bundle,
                    'developer' => $developer
                ))
            ;

            if ($activity) {
                $this->entityManager->remove($activity);
            }

            $bundle->removeRecommender($developer);
        } else {
            $activity = new Activity();
            $activity->setType(Activity::ACTIVITY_TYPE_RECOMMEND);
            $activity->setBundle($bundle);
            $activity->setDeveloper($developer);

            $this->entityManager->persist($activity);

            $bundle->addRecommender($developer);
        }

        $this->entityManager->persist($bundle);
        $this->entityManager->flush();
    }

    /**
     * @param string  $fullName
     * @param boolean $flushEntities
     *
     * @return boolean|Bundle return false if the bundle is not valid
     */
    public function createBundle($fullName, $flushEntities = true)
    {
        if (strpos($fullName, '/')) {
            list($ownerName, $bundleName) = explode('/', $fullName);

            $findBy = array('ownerName' => $ownerName, 'name' => $bundleName);
        } else {
            $findBy = array('name' => $fullName);
        }

        $bundle = $this->findBundleBy($findBy);
        if (!$bundle) {
            if (!isset($findBy['ownerName'])) {
                $bundle = $this->createEmptyBundle($fullName);
            } else {
                $bundle = $this->createFullBundle($findBy['name'], $findBy['ownerName']);
            }

            if (!$bundle) {
                return false;
            }

            $this->entityManager->persist($bundle);
            if ($flushEntities) {
                $this->entityManager->flush();
            }
        }
        return $bundle;
    }

    /**
     * @param string $name
     *
     * @return Bundle
     */
    private function createEmptyBundle($name)
    {
        $bundle = new Bundle();
        $bundle->setName($name);

        return $bundle;
    }

    /**
     * @param string $name
     * @param string $ownerName
     *
     * @return boolean|Bundle return false if the bundle is not valid
     */
    private function createFullBundle($name, $ownerName)
    {
        $bundle = $this->createEmptyBundle($name);
        $bundle->setOwnerName($ownerName);

        if (!$this->repoApi->validate($bundle)) {
            return false;
        }

        if (!$this->repoApi->updateInfos($bundle)) {
            return false;
        }

        $owner = $this->ownerManager->createOwner($ownerName, 'unknown', false);
        if (!$owner) {
            return false;
        }

        $owner->addBundle($bundle);

        return $bundle;
    }
}
