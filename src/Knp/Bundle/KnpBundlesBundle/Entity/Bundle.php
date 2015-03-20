<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineCollectionAdapter;

/**
 * An Open Source Repo living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\BundleRepository")
 * @ORM\Table(
 *      name="bundle",
 *      indexes={
 *          @ORM\Index(name="trend1", columns={"trend1"})
 *      },
 *      uniqueConstraints={@ORM\UniqueConstraint(name="full_name_unique",columns={"ownerName", "name"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Bundle
{
    const STATE_UNKNOWN       = 'unknown';
    const STATE_NOT_YET_READY = 'not yet ready';
    const STATE_READY         = 'ready';
    const STATE_DEPRECATED    = 'deprecated';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Repo name, e.g. "MarkdownBundle"
     * Like in GitHub, this name is not unique
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 2)
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $name;

    /**
     * The name of the owner who owns this bundle
     * This value is redundant with the name of the referenced Owner, for performance reasons
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 2)
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $ownerName;

    /**
     * The type of the owner who owns this bundle
     * This value is redundant with the class of the referenced Owner, for performance reasons
     *
     * @ORM\Column(type="string", length=15)
     */
    protected $ownerType = 'developer';

    /**
     * Owner of the bundle
     *
     * @ORM\ManyToOne(targetEntity="Owner", inversedBy="bundles")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     *
     * @var Collection
     */
    protected $owner;

    /**
     * Developers recommending the bundle
     *
     * @ORM\ManyToMany(targetEntity="Developer", inversedBy="recommendedBundles")
     * @ORM\JoinTable(name="bundles_usage",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="knpbundles_owner_id", referencedColumnName="id")}
     * )
     *
     * @var Collection
     */
    protected $recommenders;

    /**
     * Repo description
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * The website url, if any
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $homepage;

    /**
     * The bundle canonical configuration yaml extracted from bundle's repo
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $canonicalConfig;

    /**
     * The bundle readme text extracted from source code
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $readme;

    /**
     * The bundle license text extracted from source code
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $license;

    /**
     * The bundle license type extracted from composer.json
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $licenseType;

    /**
     * Internal score of the Repo, based on several indicators
     * Defines the Repo position in lists and searches
     *
     * @ORM\Column(type="integer")
     */
    protected $score = 0;

    /**
     * Internal scores
     *
     * @ORM\OneToMany(targetEntity="Score", mappedBy="bundle", cascade={"persist"}, fetch="EXTRA_LAZY")
     *
     * @var Collection
     */
    protected $scores;

    /**
     * Latest score's details
     *
     * @ORM\Column(type="array", nullable=true)
     *
     * @var array
     */
    protected $scoreDetails = array();

    /**
     * Repo creation date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * Repo update date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * Date of the last Git commit
     *
     * @ORM\Column(type="date")
     */
    protected $lastCommitAt;

    /**
     * Date of the last successful GitHub check
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $lastCheckAt;

    /**
     * Status of bundle
     *
     * @ORM\Column(type="string")
     */
    protected $state = self::STATE_UNKNOWN;

    /**
     * Developers who contributed to the Repo
     *
     * @ORM\ManyToMany(targetEntity="Developer", inversedBy="contributionBundles")
     * @ORM\JoinTable(name="contribution",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="developer_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @var Collection
     */
    protected $contributors;

    /**
     * Developers who are the Repo collabs
     *
     * @ORM\ManyToMany(targetEntity="Developer", inversedBy="collaboratedBundles")
     * @ORM\JoinTable(name="collaboration",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="developer_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     *
     * @var Collection
     */
    protected $collaborators;

    /**
     * Number of GitHub followers
     *
     * @ORM\Column(type="integer")
     */
    protected $nbFollowers = 0;

    /**
     * Number of GitHub forks
     *
     * @ORM\Column(type="integer")
     */
    protected $nbForks = 0;

    /**
     * True if the Repo is a fork
     *
     * @ORM\Column(type="boolean")
     */
    protected $isFork = false;

    /**
     * True if the Repo uses Travis CI
     *
     * @ORM\Column(type="boolean")
     */
    protected $usesTravisCi = false;

    /**
     * Travis Ci last build status
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $travisCiBuildStatus;

    /**
     * Trend over the last day. Max is better.
     * @ORM\Column(type="integer")
     */
    protected $trend1 = 0;

    /**
     * Composer name
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $composerName;

    /**
     * Bundle keywords
     *
     * @ORM\ManyToMany(targetEntity="Keyword", inversedBy="bundles", cascade={"persist"})
     * @ORM\JoinTable(name="bundles_keyword",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="keyword_id", referencedColumnName="id")}
     * )
     *
     * @var Collection
     */
    protected $keywords;

    /**
     * Bundle versions history
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $versionsHistory;

    /**
     * Last indexing time.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $indexedAt;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbRecommenders = 0;

    /**
     * Date when bundle was tweeted from knplabs account.
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $lastTweetedAt;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="bundle", fetch="EXTRA_LAZY", cascade={"persist"}, orphanRemoval=true)
     *
     * @var Collection
     */
    protected $activities;

    /**
     * Developers who has favorited the bundle
     *
     * @ORM\ManyToMany(targetEntity="Developer", inversedBy="favoriteBundles")
     * @ORM\JoinTable(name="bundles_favorites")
     *
     * @var Collection
     */
    protected $favorers;

    /**
     * @ORM\Column(type="integer")
     */
    protected $nbFavorers = 0;

    /**
     * @param null|string $fullName
     */
    public function __construct($fullName = null)
    {
        if ($fullName) {
            list($this->ownerName, $this->name) = explode('/', $fullName);
        }

        $this->createdAt    = new \DateTime();
        $this->updatedAt    = new \DateTime();
        $this->lastCommitAt = new \DateTime('2010-01-01');

        $this->activities   = new ArrayCollection();
        $this->contributors = new ArrayCollection();
        $this->scores       = new ArrayCollection();
        $this->keywords     = new ArrayCollection();
        $this->favorers     = new ArrayCollection();
    }

    public function isInitialized()
    {
        // Using the fact that a repo should have at least one declared fork:
        // itself
        return $this->nbForks > 0;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get homepage
     *
     * @return string
     */
    public function getHomepage()
    {
        return $this->homepage;
    }

    /**
     * Set homepage
     *
     * @param string $homepage
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * Get isFork
     *
     * @return boolean
     */
    public function getIsFork()
    {
        return $this->isFork;
    }

    /**
     * Set isFork
     *
     * @param boolean $isFork
     */
    public function setIsFork($isFork)
    {
        $this->isFork = $isFork;
    }

    /**
     * Get whether bundle uses Travis CI
     *
     * @return boolean
     */
    public function getUsesTravisCi()
    {
        return $this->usesTravisCi;
    }

    /**
     * Set whether bundle uses Travis CI
     *
     * @param boolean $uses
     */
    public function setUsesTravisCi($uses)
    {
        $this->usesTravisCi = $uses;
    }

    /**
     * Get Travis Ci last build status
     *
     * @return string
     */
    public function getTravisCiBuildStatus()
    {
        return $this->travisCiBuildStatus;
    }

    /**
     * Set Travis Ci last build status
     *
     * @param string $status
     */
    public function setTravisCiBuildStatus($status)
    {
        $this->travisCiBuildStatus = $status;
    }

    /**
     * Get Composer name
     *
     * @return string
     */
    public function getComposerName()
    {
        return $this->composerName;
    }

    /**
     * Set Composer name
     *
     * @param  string
     */
    public function setComposerName($name)
    {
        $this->composerName = $name;
    }

    /**
     * Get readme
     *
     * @return string
     */
    public function getReadme()
    {
        return $this->readme;
    }

    /**
     * Set readme
     *
     * @param string $readme
     */
    public function setReadme($readme)
    {
        $this->readme = $readme;
    }

    /**
     * Get license data
     *
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * Set license data
     *
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * Get license type
     *
     * @return string
     */
    public function getLicenseType()
    {
        return $this->licenseType;
    }

    /**
     * Set license type
     *
     * @param string $license
     */
    public function setLicenseType($license)
    {
        $this->licenseType = $license;
    }

    /**
     * Get score
     *
     * @return integer
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set score
     *
     * @param integer $score
     */
    public function setScore($score)
    {
        $this->score = (int) $score;
    }

    /**
     * Get all historical scores indexed by date
     *
     * @param integer $limit
     *
     * @return array
     */
    public function getScores($limit = null)
    {
        if (null === $limit) {
            return $this->scores;
        }

        return $this->scores->slice(0, $limit);
    }

    public function getLatestScoreDetails()
    {
        return $this->scores->last();
    }

    public function addScoreDetail($name, $value)
    {
        $this->scoreDetails[$name] = $value;
    }

    /**
     * Returns details about the bundle's score
     */
    public function getScoreDetails()
    {
        return $this->scoreDetails ?: array();
    }

    public function setScoreDetails(array $details)
    {
        $this->scoreDetails = serialize($details);
    }

    /**
     * Calculate the score of this bundle based on several factors.
     *
     * The score is used as the default sort field in many places.
     * #TODO discuss me, improve me
     */
    public function recalculateScore()
    {
        $score = array_sum($this->getScoreDetails());

        $this->setScore($score);
    }

    /**
     * Updates bundle score with given amount of points
     *
     * @param  integer $points
     */
    public function updateScore($points = 1)
    {
        $this->setScore($this->score + $points);
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
     * Returns the number of days elapsed since the last commit on the master branch
     *
     * @return integer
     */
    public function getDaysSinceLastCommit()
    {
        return date_create()->diff($this->lastCommitAt)->days;
    }

    /**
     * Get forks
     *
     * @return integer
     */
    public function getNbForks()
    {
        return $this->nbForks;
    }

    /**
     * Set forks
     *
     * @param integer $nbForks
     */
    public function setNbForks($nbForks)
    {
        $this->nbForks = $nbForks;
    }

    /**
     * Get followers
     *
     * @return integer
     */
    public function getNbFollowers()
    {
        return $this->nbFollowers;
    }

    /**
     * Set followers
     *
     * @param integer $nbFollowers
     */
    public function setNbFollowers($nbFollowers)
    {
        $this->nbFollowers = $nbFollowers;
    }

    /**
     * Get the GitHub url of this bundle
     *
     * @return string
     */
    public function getGitHubUrl()
    {
        return sprintf('http://github.com/%s/%s', $this->ownerName, $this->name);
    }

    /**
     * Get the Travis Ci url of this bundle
     *
     * @return string
     */
    public function getTravisUrl()
    {
        return $this->usesTravisCi ? sprintf('http://travis-ci.org/%s/%s', $this->ownerName, $this->name) : false;
    }

    /**
     * Get the Packagist url of this repo
     *
     * @return string
     */
    public function getPackagistUrl()
    {
        return $this->composerName ? sprintf('http://packagist.org/packages/%s', $this->composerName) : false;
    }

    /**
     * Get the Git repo url
     *
     * @return string
     */
    public function getGitUrl()
    {
        return sprintf('git://github.com/%s/%s.git', $this->ownerName, $this->name);
    }

    /**
     * Get full name, including ownerName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->ownerName.'/'.$this->name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param  string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get ownername
     *
     * @return string
     */
    public function getOwnerName()
    {
        return $this->ownerName;
    }

    /**
     * Set ownerName
     *
     * @param string $ownerName
     */
    public function setOwnerName($ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * @return Owner
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param Owner $owner
     */
    public function setOwner(Owner $owner)
    {
        $this->owner     = $owner;
        $this->ownerName = $owner->getName();
        $this->ownerType = $owner instanceof Organization ? 'organization' : 'developer';
    }

    /**
     * @return string
     */
    public function getOwnerType()
    {
        return $this->ownerType;
    }

    /**
     * Get description
     *
     * @param null|integer $cutAfter
     *
     * @return string
     */
    public function getDescription($cutAfter = null)
    {
        if (null === $cutAfter) {
            return $this->description;
        }

        return $this->description ? substr($this->description, 0, $cutAfter) : null;
    }

    /**
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the bundle creation date
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the bundle creation date
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the bundle update date
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get the date of last check
     *
     * @return \DateTime
     */
    public function getLastCheckAt()
    {
        return $this->lastCheckAt;
    }

    /**
     * Set the date of last check
     *
     * @param \DateTime $lastCheckAt
     */
    public function setLastCheckAt(\DateTime $lastCheckAt)
    {
        $this->lastCheckAt = $lastCheckAt;
    }

    /**
     * Get contributors
     *
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getContributors($page = null, $limit = 20)
    {
        if (null === $page) {
            return $this->contributors;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->contributors));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    /**
     * Get the number of contributors
     *
     * @return integer
     */
    public function getNbContributors()
    {
        return count($this->contributors);
    }

    /**
     * Get the trend over the last day
     *
     * @return integer
     */
    public function getTrend1()
    {
        return $this->trend1;
    }

    /**
     * Get the status of bundle
     *
     * @return string
     */
    public function getState()
    {
        return empty($this->state) ? self::STATE_UNKNOWN : $this->state;
    }

    /**
     * Set status of bundle
     *
     * @param string
     */
    public function setState($state)
    {
        if (!in_array($state, array(self::STATE_UNKNOWN, self::STATE_NOT_YET_READY, self::STATE_READY, self::STATE_DEPRECATED))) {
            $state = self::STATE_UNKNOWN;
        }

        $this->state = $state;
    }

    /**
     * Set contributors
     *
     * @param array $contributors
     */
    public function setContributors(array $contributors)
    {
        $this->contributors = new ArrayCollection($contributors);
    }

    public function getContributorNames()
    {
        $names = array();
        foreach ($this->contributors as $contributor) {
            $names[] = $contributor->getName();
        }

        return $names;
    }

    /**
     * Get the first part of the name, without Bundle
     *
     * @return string
     */
    public function getShortName()
    {
        return preg_replace('/^(.+)Bundle$/', '$1', $this->getName());
    }

    /**
     * Get an array representing the Repo
     *
     * @return array
     */
    public function toBigArray()
    {
        return $this->toSmallArray() + array(
            'readme' => $this->getReadme()
        );
    }

    public function toSmallArray()
    {
        return array(
            'type' => $this->getClass(),
            'name' => $this->getName(),
            'ownerName' => $this->getOwnerName(),
            'description' => $this->getDescription(),
            'homepage' => $this->getHomepage(),
            'score' => $this->getScore(),
            'nbFollowers' => $this->getNbFollowers(),
            'nbForks' => $this->getNbForks(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'lastCommitAt' => $this->getLastCommitAt()->getTimestamp(),
            'contributors' => $this->getContributorNames()
        );
    }

    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{'set'.$key}($value);
        }
        $this->recalculateScore();
    }

    public function __toString()
    {
        return $this->getFullName();
    }

    public function getClass()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\')+1);
    }

    /**
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getRecommenders($page = null, $limit = 20)
    {
        if (null === $page) {
            return $this->recommenders;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->recommenders));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    public function getNbRecommenders()
    {
        return count($this->recommenders);
    }

    public function addRecommender(Developer $developer)
    {
        $developer->addRecommendedBundle($this);

        $this->recommenders[] = $developer;
        $this->nbRecommenders++;

        $this->updateScore(5);
    }

    public function removeRecommender(Developer $developer)
    {
        $developer->getRecommendedBundles()->removeElement($this);

        $this->recommenders->removeElement($developer);
        $this->nbRecommenders--;

        $this->updateScore(-5);
    }

    /**
     * @param integer $nbRecommenders
     */
    public function setNbRecommenders($nbRecommenders)
    {
        $this->nbRecommenders = $nbRecommenders;
    }

    /**
     * @param Owner $owner
     *
     * @return boolean
     */
    public function isOwnerOrContributor(Owner $owner)
    {
        if ($this->owner instanceof Developer && $this->owner->isEqualTo($owner)) {
            return true;
        }

        return $this->contributors->contains($owner);
    }

    /**
     * @return Collection
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @return integer Total nb of keywords for this bundle
     */
    public function countKeywords()
    {
        return count($this->keywords);
    }

    /**
     * @param Keyword $keyword
     *
     * @return boolean
     */
    public function hasKeyword(Keyword $keyword)
    {
        return $this->keywords->contains($keyword);
    }

    /**
     * @param Keyword $keyword
     */
    public function addKeyword(Keyword $keyword)
    {
        if (!$this->hasKeyword($keyword)) {
            $this->keywords[] = $keyword;
        }
    }

    /**
     * @param Activity $activity
     */
    public function addActivity(Activity $activity)
    {
        $this->activities[] = $activity;
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
        if (!in_array($type, array(Activity::ACTIVITY_TYPE_COMMIT, Activity::ACTIVITY_TYPE_RECOMMEND, Activity::ACTIVITY_TYPE_TRAVIS_BUILD))) {
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

    /**
     * @param null|integer $page
     * @param integer      $limit
     *
     * @return \Traversable
     */
    public function getFavorers($page = null, $limit = 20)
    {
        if (null === $page) {
            return $this->favorers;
        }

        $paginator = new Pagerfanta(new DoctrineCollectionAdapter($this->favorers));
        $paginator
            ->setMaxPerPage($limit)
            ->setCurrentPage($page)
        ;

        return $paginator->getCurrentPageResults();
    }

    public function getNbFavorers()
    {
        return $this->nbFavorers;
    }

    public function addFavorer(Developer $developer)
    {
        $developer->addFavoriteBundle($this);

        $this->favorers[] = $developer;
        $this->nbFavorers++;
    }

    public function removeFavorer(Developer $developer)
    {
        $developer->getFavoriteBundles()->removeElement($this);

        $this->favorers->removeElement($developer);
        $this->nbFavorers--;
    }

    /**
     * @param integer $nbFavorers
     */
    public function setNbFavorers($nbFavorers)
    {
        $this->nbFavorers = $nbFavorers;
    }

    /**
     * Get required versions of Symfony from bundle Versions History
     *
     * @return array|null
     */
    public function getSymfonyVersions()
    {
        return empty($this->versionsHistory['symfony']) ? null : $this->versionsHistory['symfony'];
    }

    /**
     * Get bundle versions history
     *
     * @return array
     */
    public function getVersionsHistory()
    {
        return $this->versionsHistory;
    }

    /**
     * Set bundle versions history
     *
     * @param array $versions
     */
    public function setVersionsHistory($versions)
    {
        $this->versionsHistory = $versions;
    }

    /**
     * Set indexedAt
     *
     * @param \DateTime $indexedAt
     */
    public function setIndexedAt(\DateTime $indexedAt)
    {
        $this->indexedAt = $indexedAt;
    }

    /**
     * Get indexedAt
     *
     * @return \DateTime
     */
    public function getIndexedAt()
    {
        return $this->indexedAt;
    }

    /**
     * Get canonicalConfig
     *
     * @return string
     */
    public function getCanonicalConfig()
    {
        return $this->canonicalConfig;
    }

    /**
     * Set canonicalConfig
     *
     * @param $canonicalConfig
     */
    public function setCanonicalConfig($canonicalConfig)
    {
        $this->canonicalConfig = $canonicalConfig;
    }

    /**
     * Set lastTweetedAt
     *
     * @param \DateTime $lastTweetedAt
     */
    public function setLastTweetedAt(\DateTime $lastTweetedAt)
    {
        $this->lastTweetedAt = $lastTweetedAt;
    }

    /**
     * Get lastTweetedAt
     *
     * @return null|\DateTime
     */
    public function getLastTweetedAt()
    {
        return $this->lastTweetedAt;
    }

    /** @ORM\PreUpdate */
    public function markAsUpdated()
    {
        $this->updatedAt = new \DateTime();
    }

    /** @ORM\PrePersist */
    public function markAsCreated()
    {
        $this->updatedAt = new \DateTime();
        if (!$this->createdAt) {
            $this->createdAt = $this->updatedAt;
        }
    }

    /**
     * Unique string which shows current status of the bundle
     *
     * @return string
     */
    public function getStatusHash()
    {
        $statusFields = array(
            'description'  => $this->getDescription(),
            'nbFollowers'  => $this->getNbFollowers(),
            'nbForks'      => $this->getNbForks(),
            'lastCommitAt' => $this->getLastCommitAt()->getTimestamp(),
            'readme'       => $this->getReadme()
        );

        return md5(serialize($statusFields));
    }

    /**
     * @return boolean
     */
    public function hasChanges()
    {
        if ($this->getLatestScoreDetails()) {
            return $this->getLatestScoreDetails()->getHash() !== $this->getStatusHash();
        }

        return true;
    }
}
