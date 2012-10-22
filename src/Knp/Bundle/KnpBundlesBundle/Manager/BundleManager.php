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
     * @var OwnerManager
     */
    private $ownerManager;

    /**
     * @var array
     */
    private $options = array(
        'min_score_diff'      => null,
        'min_score_threshold' => null
    );

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
     * @param string $name
     * @param string $value
     *
     * @throws \InvalidArgumentException
     */
    public function setOption($name, $value)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \InvalidArgumentException();
        }

        $this->options[$name] = $value;
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

    public function updateTrends()
    {
        // Reset trends
        $q = $this->entityManager->createQuery('UPDATE Knp\Bundle\KnpBundlesBundle\Entity\Bundle bundle SET bundle.trend1 = 0');
        $q->execute();

        $query = <<<EOF
UPDATE bundle

JOIN (
    SELECT date, bundle_id,
    (
        SELECT current.value - value AS diff
        FROM score
        WHERE bundle_id = current.bundle_id
        AND date < current.date
        ORDER BY date DESC
        LIMIT 1
    ) AS diff
    FROM score AS current
    WHERE date = CURRENT_DATE
) score
  ON score.bundle_id = bundle.id
  AND score.diff > :minDiff

SET trend1 = score.diff
WHERE score >= :minThreshold
EOF;

        return $this->entityManager->getConnection()->executeUpdate($query, array('minDiff' => $this->options['min_score_diff'], 'minThreshold' => $this->options['min_score_threshold']));
    }

    /**
     * @param string  $fullName
     * @param boolean $flushEntities
     *
     * @return boolean|Bundle
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
     * @return boolean|Bundle
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
