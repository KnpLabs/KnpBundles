<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;

/**
 * An user living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\DeveloperRepository")
 */
class Developer extends Owner implements UserInterface
{
    /**
     * The user company name
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * Date of the last Git commit
     *
     * @ORM\Column(type="date")
     */
    private $lastCommitAt;

    /**
     * Organizations where developer belongs to
     *
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="members")
     *
     * @var Collection
     */
    private $organizations;

    /**
     * Bundles recommended by this user
     *
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="recommenders")
     *
     * @var Collection
     */
    private $recommendedBundles;

    /**
     * Bundles this User contributed to
     *
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="contributors")
     *
     * @var Collection
     */
    private $contributionBundles;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="developer", fetch="EXTRA_LAZY", cascade={"persist"})
     *
     * @var Collection
     */
    private $activities;

    public function __construct()
    {
        $this->activities          = new ArrayCollection();
        $this->organizations       = new ArrayCollection();
        $this->recommendedBundles  = new ArrayCollection();
        $this->contributionBundles = new ArrayCollection();

        parent::__construct();
    }

    /**
     * @param $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Get the date of the last commit
     *
     * @return \DateTime
     */
    public function getLastCommitAt()
    {
        return $this->lastCommitAt;
    }

    /**
     * Set lastCommitAt
     *
     * @param \DateTime $lastCommitAt
     */
    public function setLastCommitAt(\DateTime $lastCommitAt)
    {
        $this->lastCommitAt = $lastCommitAt;
    }

    /**
     * Add organizations
     *
     * @param Organization $organization
     */
    public function addOrganization(Organization $organization)
    {
        $this->organizations[] = $organization;

        $organization->addMember($this);
    }

    /**
     * Remove organizations
     *
     * @param Organization $organization
     */
    public function removeOrganization(Organization $organization)
    {
        $this->organizations->removeElement($organization);

        $organization->removeMember($this);
    }

    /**
     * Get organizations
     *
     * @return Collection
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Add recommended Bundle
     *
     * @param Bundle $recommendedBundle
     */
    public function addRecommendedBundle(Bundle $recommendedBundle)
    {
        $this->recommendedBundles[] = $recommendedBundle;
    }

    /**
     * Remove recommended Bundle
     *
     * @param Bundle $recommendedBundle
     */
    public function removeRecommendedBundle(Bundle $recommendedBundle)
    {
        $this->recommendedBundles->removeElement($recommendedBundle);
    }

    /**
     * Get recommended Bundles
     *
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getRecommendedBundles($page = null, $limit = 15)
    {
        if (null === $page) {
            return $this->recommendedBundles;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->recommendedBundles));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    /**
     * Check that owner is using bundles
     *
     * @param Bundle $bundle
     *
     * @return boolean
     */
    public function isUsingBundle(Bundle $bundle)
    {
        return $this->recommendedBundles->contains($bundle);
    }

    /**
     * Add contribution Bundle
     *
     * @param Bundle $contributionBundle
     */
    public function addContributionBundle(Bundle $contributionBundle)
    {
        $this->contributionBundles[] = $contributionBundle;
    }

    /**
     * Remove contribution Bundle
     *
     * @param Bundle $contributionBundle
     */
    public function removeContributionBundle(Bundle $contributionBundle)
    {
        $this->contributionBundles->removeElement($contributionBundle);
    }

    /**
     * Get contributionBundles
     *
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getContributionBundles($page = null, $limit = 15)
    {
        if (null === $page) {
            return $this->contributionBundles;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->contributionBundles));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    /**
     * @param Activity $activity
     */
    public function addActivity(Activity $activity)
    {
        $this->activities[] = $activity;
    }

    /**
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
    {
        $this->activities->removeElement($activity);
    }

    /**
     * Get activities
     *
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getActivities($page = null, $limit = 15)
    {
        if (null === $page) {
            return $this->activities;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->activities));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    public function getLatestActivities($type = Activity::ACTIVITY_TYPE_COMMIT)
    {
        if (!in_array($type, array(Activity::ACTIVITY_TYPE_COMMIT, Activity::ACTIVITY_TYPE_RECOMMEND))) {
            throw new \InvalidArgumentException();
        }

        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('type', $type))
            ->orderBy(array("createdAt" => "DESC"))
            ->setFirstResult(0)
            ->setMaxResults(30)
        ;

        return $this->activities->matching($criteria);
    }

    public function toSmallArray()
    {
        return array(
            'name'          => $this->getName(),
            'email'         => $this->getEmail(),
            'avatarUrl'     => $this->getAvatarUrl(),
            'fullName'      => $this->getFullName(),
            'company'       => $this->getCompany(),
            'location'      => $this->getLocation(),
            'blog'          => $this->getUrl(),
            'bundles'       => $this->getBundleNames(),
            'lastCommitAt'  => $this->getLastCommitAt() ? $this->getLastCommitAt()->getTimestamp() : null,
            'score'         => $this->getScore(),
        );
    }

    /**
     * @return array
     */
    public function getAllBundles()
    {
        return array_merge($this->bundles->toArray(), $this->contributionBundles->toArray());
    }

    /* ---------- Security User ---------- */
    public function getUsername()
    {
        return $this->name;
    }

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(Developer $developer)
    {
        return $developer->getName() === $this->getName();
    }
    /* !--------- Security User ---------! */
}
