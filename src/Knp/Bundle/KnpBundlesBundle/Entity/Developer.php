<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $githubId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sensioId;

    /**
     * Organizations where developer part of
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="members")
     */
    private $organizations;

    /**
     * Bundles this User recommended to
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="recommenders")
     */
    private $recommendedBundles;

    /**
     * Bundles this User contributed to
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="contributors")
     */
    private $contributionBundles;

    /**
     * local cache, not persisted
     */
    private $lastCommitsCache;

    public function __construct()
    {
        $this->organizations = new ArrayCollection();
        $this->recommendedBundles = new ArrayCollection();
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
     * @param $githubId
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }

    /**
     * @return mixed
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * @param $sensioId
     */
    public function setSensioId($sensioId)
    {
        $this->sensioId = $sensioId;
    }

    /**
     * @return mixed
     */
    public function getSensioId()
    {
        return $this->sensioId;
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
     * @return ArrayCollection
     */
    public function getOrganizations()
    {
        return $this->organizations;
    }

    /**
     * Add recommendedBundles
     *
     * @param Bundle $recommendedBundles
     */
    public function addRecommendedBundle(Bundle $recommendedBundles)
    {
        $this->recommendedBundles[] = $recommendedBundles;
    }

    /**
     * Remove recommendedBundles
     *
     * @param Bundle $recommendedBundles
     */
    public function removeRecommendedBundle(Bundle $recommendedBundles)
    {
        $this->recommendedBundles->removeElement($recommendedBundles);
    }

    /**
     * Get recommendedBundles
     *
     * @return ArrayCollection
     */
    public function getRecommendedBundles()
    {
        return $this->recommendedBundles;
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
     * Add contributionBundles
     *
     * @param Bundle $contributionBundles
     */
    public function addContributionBundle(Bundle $contributionBundles)
    {
        $this->contributionBundles[] = $contributionBundles;
    }

    /**
     * Remove contributionBundles
     *
     * @param Bundle $contributionBundles
     */
    public function removeContributionBundle(Bundle $contributionBundles)
    {
        $this->contributionBundles->removeElement($contributionBundles);
    }

    /**
     * Get contributionBundles
     *
     * @return ArrayCollection
     */
    public function getContributionBundles()
    {
        return $this->contributionBundles;
    }

    /**
     * @param $lastCommitsCache
     */
    public function setLastCommitsCache($lastCommitsCache)
    {
        $this->lastCommitsCache = $lastCommitsCache;
    }

    /**
     * @return mixed
     */
    public function getLastCommitsCache()
    {
        return $this->lastCommitsCache;
    }

    /**
     * Get the date of the last commit
     *
     * @return \DateTime
     */
    public function getLastCommitAt()
    {
        $lastCommits = $this->getLastCommits(1);
        if (empty($lastCommits)) {
            return null;
        }
        $lastCommit = $lastCommits[0];
        $date = new \DateTime($lastCommit['committed_date']);

        return $date;
    }

    /**
     * Get the more recent commits by this user
     *
     * @param integer $nb
     * @return array
     */
    public function getLastCommits($nb = 10)
    {
        if (null === $this->lastCommitsCache) {
            $commits = array();
            foreach ($this->getAllBundles() as $bundle) {
                foreach ($bundle->getLastCommits() as $commit) {
                    if (isset($commit['author']['login']) && $commit['author']['login'] === $this->name) {
                        $commits[] = $commit;
                    }
                }
            }
            usort($commits, function($a, $b) {
                return strtotime($a['committed_date']) < strtotime($b['committed_date']);
            });
            $this->lastCommitsCache = $commits;
        }
        $commits = array_slice($this->lastCommitsCache, 0, $nb);

        return $commits;
    }

    public function toSmallArray()
    {
        return array(
            'name'          => $this->getName(),
            'email'         => $this->getEmail(),
            'gravatarHash'  => $this->getAvatarUrl(),
            'fullName'      => $this->getFullName(),
            'company'       => $this->getCompany(),
            'location'      => $this->getLocation(),
            'blog'          => $this->getUrl(),
            'bundles'       => $this->getBundleNames(),
            'lastCommitAt'  => $this->getLastCommitAt() ? $this->getLastCommitAt()->getTimestamp() : null,
            'score'         => $this->getScore(),
        );
    }

    public function toBigArray()
    {
        return $this->toSmallArray() + array(
            'lastCommits' => $this->getLastCommits()
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
