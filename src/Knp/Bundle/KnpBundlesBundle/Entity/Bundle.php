<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * An Open Source Repo living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\BundleRepository")
 * @ORM\Table(
 *      name="bundle",
 *      indexes={
 *          @ORM\Index(name="trend1", columns={"trend1"})
 *      },
 *      uniqueConstraints={@ORM\UniqueConstraint(name="full_name_unique",columns={"username", "name"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Bundle
{
    // TODO: switch to annotations
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('name', new Constraints\MinLength(2));
        $metadata->addPropertyConstraint('username', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('username', new Constraints\MinLength(2));
    }

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
     * @ORM\Column(type="string", length=127)
     */
    protected $name = null;

    /**
     * The name of the user who owns this bundle
     * This value is redundant with the name of the referenced User, for performance reasons
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $username = null;

    /**
     * User who owns the bundle
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bundles")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user = null;

    /**
     * Recommenders recommending the bundle
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="recommendedBundles")
     * @ORM\JoinTable(name="bundles_usage",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="knpbundles_user_id", referencedColumnName="id")}
     *      )
     */
    protected $recommenders = null;

    /**
     * Repo description
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description = null;

    /**
     * The website url, if any
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $homepage = null;

    /**
     * The bundle readme text extracted from source code
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $readme = null;

    /**
     * Internal score of the Repo, based on several indicators
     * Defines the Repo position in lists and searches
     *
     * @ORM\Column(type="integer")
     */
    protected $score = null;

    /**
     * Internal scores
     *
     * @ORM\OneToMany(targetEntity="Score", mappedBy="bundle")
     */
    protected $scores = null;

    /**
     * Repo creation date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt = null;

    /**
     * Repo update date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt = null;

    /**
     * Date of the last Git commit
     *
     * @ORM\Column(type="date")
     */
    protected $lastCommitAt = null;

    /**
     * The last commits on this bundle repo
     *
     * @ORM\Column(type="text")
     */
    protected $lastCommits = null;

    /**
     * Released tags are Git tags
     *
     * @ORM\Column(type="text")
     */
    protected $tags = null;

    /**
     * Recommenders who contributed to the Repo
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="contributionBundles")
     * @ORM\JoinTable(name="contribution",
     *      joinColumns={@ORM\JoinColumn(name="bundle_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")}
     *)
     *
     * @var ArrayCollection
     */
    protected $contributors = null;

    /**
     * Number of GitHub followers
     *
     * @ORM\Column(type="integer")
     */
    protected $nbFollowers = null;

    /**
     * Number of GitHub forks
     *
     * @ORM\Column(type="integer")
     */
    protected $nbForks = null;

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
    protected $travisCiBuildStatus = null;
        
    /**
     * Trend over the last day. Max is better.
     * @ORM\Column(type="integer")
     */
    protected $trend1 = null;

    /**
     * Composer name
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $composerName = null;
    
    public function __construct($fullName = null)
    {
        if ($fullName) {
            list($this->username, $this->name) = explode('/', $fullName);
        }

        $this->contributors = new ArrayCollection();
        $this->createdAt = new \DateTime('NOW');
        $this->updatedAt = new \DateTime('NOW');
        $this->score = 0;
        $this->scores = new ArrayCollection();;
        $this->lastCommitAt = new \DateTime('2010-01-01');
        $this->lastCommits = serialize(array());
        $this->tags = serialize(array());
        $this->nbFollowers = 0;
        $this->nbForks = 0;
        $this->usesTravisCi = false;
        $this->travisCiBuildStatus = null;
        $this->trend1 = 0;
        $this->composerName = null;
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
     * @param  string
     */
    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    /**
     * Get isFork
     *
     * @return bool
     */
    public function getIsFork()
    {
        return $this->isFork;
    }

    /**
     * Set isFork
     *
     * @param  bool
     */
    public function setIsFork($isFork)
    {
        $this->isFork = $isFork;
    }

    /**
     * Get whether bundle uses Travis CI
     *
     * @return bool
     */
    public function getUsesTravisCi()
    {
        return $this->usesTravisCi;
    }

    /**
     * Set whether bundle uses Travis CI
     *
     * @param  bool
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
     * @param  string
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
     * Get tags
     *
     * @return array
     */
    public function getTags()
    {
        return unserialize($this->tags);
    }

    /**
     * Set tags
     *
     * @param  array
     */
    public function setTags(array $tags)
    {
        $this->tags = serialize($tags);
    }

    public function getLastTagName()
    {
        $tags = $this->getTags();
        if (empty($tags)) {
            return null;
        }

        return reset($tags);
    }

    /**
     * Get lastCommits
     *
     * @return array
     */
    public function getLastCommits($nb = 10)
    {
        $lastCommits = array_slice(unserialize($this->lastCommits), 0, $nb);
        foreach ($lastCommits as $i => $commit) {
            $lastCommits[$i]['message_first_line'] = strtok($commit['message'], "\n\r");
        }

        return $lastCommits;
    }

    /**
     * Set lastCommits
     *
     * @param  array
     */
    public function setLastCommits(array $lastCommits)
    {
        foreach($lastCommits as $index => $commit) {
            $lastCommits[$index]['bundle_name'] = $this->getName();
            $lastCommits[$index]['bundle_username'] = $this->getUsername();
        }
        $this->lastCommits = serialize($lastCommits);

        $lastCommitAt = new \DateTime();
        $lastCommitAt->setTimestamp(strtotime($lastCommits[0]['committed_date']));
        $this->setLastCommitAt($lastCommitAt);
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
     * @param  string
     */
    public function setReadme($readme)
    {
        $this->readme = $readme;
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
     * @param  integer
     */
    public function setScore($score)
    {
        $this->score = (int) $score;
    }

    /**
     * Get all historical scores indexed by date
     *
     * @return array
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * Calculate the score of this bundle based on several factors.
     *
     * The score is used as the default sort field in many places.
     * #TODO discuss me, improve me
     */
    public function recalculateScore()
    {
        // 1 follower = 1 point
        $score = $this->getNbFollowers();

        // Small boost for recently updated bundles
        if ($this->getDaysSinceLastCommit() < 30) {
            $score += (30 - $this->getDaysSinceLastCommit()) / 5;
        }

        // Small boost for bundles that have a real README file
        if (mb_strlen($this->getReadme()) > 300) {
            $score += 5;
        }

        // Small boost for bundles that uses travis ci
        if ($this->getUsesTravisCi()) {
            $score += 5;
        }

        // Small boost for bundles with passing tests according to Travis
        if ($this->getTravisCiBuildStatus()) {
            $score += 5;
        }

        // Small boost for repos that provide composer package
        if ($this->getComposerName()) {
            $score += 5;
        }

        // Small boost for repos that have recommenders recommending it
        $score += $this->getNbRecommenders();

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
     * @param  \DateTime
     * @return null
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
        return date_create()->diff($this->getLastCommitAt())->days;
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
     * @param  integer
     * @return null
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
     * @param  integer
     * @return null
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
        return sprintf('http://github.com/%s/%s', $this->getUsername(), $this->getName());
    }

    /**
     * Get the Travis Ci url of this bundle
     *
     * @return string
     */
    public function getTravisUrl()
    {
        return $this->getUsesTravisCi() ? sprintf('http://travis-ci.org/%s/%s', $this->getUsername(), $this->getName()) : false;
    }

    /**
     * Get the Packagist url of this repo
     *
     * @return string
     */
    public function getPackagistUrl()
    {
        return $this->getComposerName() ? sprintf('http://packagist.org/packages/%s', $this->getComposerName()) : false;
    }
    
    /**
     * Get the Git repo url
     *
     * @return string
     */
    public function getGitUrl()
    {
        return sprintf('git://github.com/%s/%s.git', $this->getUsername(), $this->getName());
    }

    /**
     * Get full name, including username
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getUsername().'/'.$this->name;
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
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
     *
     * @param  string
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param  string
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
     * Get contributors
     *
     * @return ArrayCollection
     */
    public function getContributors()
    {
        return $this->contributors;
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
     * Set contributors
     *
     * @param  array
     */
    public function setContributors(array $contributors)
    {
        $this->contributors = new ArrayCollection($contributors);
    }

    public function getContributorNames()
    {
        $names = array();
        foreach ($this->getContributors() as $contributor) {
            $names[] = $contributor->getName();
        }

        return $names;
    }

    /** @ORM\PreUpdate */
    public function markAsUpdated()
    {
        $this->updatedAt = new \DateTime();
    }

    /** @ORM\PrePersist */
    public function markAsCreated()
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
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
            'lastCommits' => $this->getLastCommits(),
            'readme' => $this->getReadme()
        );
    }

    public function toSmallArray()
    {
        return array(
            'type' => $this->getClass(),
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'description' => $this->getDescription(),
            'homepage' => $this->getHomepage(),
            'score' => $this->getScore(),
            'nbFollowers' => $this->getNbFollowers(),
            'nbForks' => $this->getNbForks(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'lastCommitAt' => $this->getLastCommitAt()->getTimestamp(),
            'tags' => $this->getTags(),
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
        return $this->getUsername().'/'.$this->getName();
    }

    public function getClass()
    {
        $class = get_class($this);

        return substr($class, strrpos($class, '\\')+1);
    }

    public function getRecommenders()
    {
        return $this->recommenders;
    }

    public function getNbRecommenders()
    {
        return count($this->recommenders);
    }

    public function addRecommender(User $user)
    {
        $this->recommenders[] = $user;
    }
}
