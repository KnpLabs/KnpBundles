<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An Open Source Repo living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Entity\RepoRepository")
 * @ORM\Table(
 *      name="repo",
 *      indexes={@ORM\Index(name="discriminator", columns={"discr"})},
 *      uniqueConstraints={@ORM\UniqueConstraint(name="full_name_unique",columns={"username", "name"})}
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"bundle" = "Bundle", "project" = "Project"})
 * @ORM\HasLifecycleCallbacks
 */
abstract class Repo
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('name', new Constraints\MinLength(2));
        $metadata->addPropertyConstraint('username', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('username', new Constraints\MinLength(2));
    }

    public static function create($fullName)
    {
        if (preg_match('/Bundle$/', $fullName)) {
            return new Bundle($fullName);
        }

        return new Project($fullName);
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="repos")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user = null;

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
     * Users who contributed to the Repo
     *
     * @ORM\ManyToMany(targetEntity="User", inversedBy="contributionRepos")
     * @ORM\JoinTable(name="contribution",
     *      joinColumns={@ORM\JoinColumn(name="repo_id", referencedColumnName="id", onDelete="CASCADE")},
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
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $travisCiBuildStatus = null;
        
    public function __construct($fullName = null)
    {
        if ($fullName) {
            list($this->username, $this->name) = explode('/', $fullName);
        }

        $this->contributors = new ArrayCollection();
        $this->createdAt = new \DateTime('NOW');
        $this->updatedAt = new \DateTime('NOW');
        $this->score = 0;
        $this->lastCommitAt = new \DateTime('2010-01-01');
        $this->lastCommits = serialize(array());
        $this->tags = serialize(array());
        $this->nbFollowers = 0;
        $this->nbForks = 0;
        $this->usesTravisCi = false;
        $this->travisCiBuildStatus = null;
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
     * @return null
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
     * @return null
     */
    public function setIsFork($isFork)
    {
        $this->isFork = $isFork;
    }

    /**
     * Get whether repo uses Travis CI
     *
     * @return bool
     */
    public function getUsesTravisCi()
    {
        return $this->usesTravisCi;
    }

    /**
     * Set whether repo uses Travis CI
     *
     * @param  bool
     * @return null
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
     * @return null
     */
    public function setTravisCiBuildStatus($status)
    {
        $this->travisCiBuildStatus = $status;
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
     * @return null
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
     * @return null
     */
    public function setLastCommits(array $lastCommits)
    {
        foreach($lastCommits as $index => $commit) {
            $lastCommits[$index]['repo_name'] = $this->getName();
            $lastCommits[$index]['repo_username'] = $this->getUsername();
        }
        $this->lastCommits = serialize($lastCommits);

        $lastCommitAt = new \DateTime();
        $lastCommitAt->setTimestamp(strtotime($lastCommits[0]['committed_date']));
        $this->setLastCommitAt($lastCommitAt);
    }

    /**
     * Get readme
     * @return string
     */
    public function getReadme()
    {
        return $this->readme;
    }

    /**
     * Set readme
     * @param  string
     * @return null
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
     * @param  integer
     * @return null
     */
    public function setScore($score)
    {
        $this->score = (int) $score;
    }

    /**
     * Calculate the score of this repo based on several factors.
     *
     * The score is used as the default sort field in many places.
     * #TODO discuss me, improve me
     */
    public function recalculateScore()
    {
        // 1 follower = 1 point
        $score = $this->getNbFollowers();

        // Small boost for recently updated repos
        if ($this->getDaysSinceLastCommit() < 30) {
            $score += (30 - $this->getDaysSinceLastCommit()) / 5;
        }

        // Small boost for repos that have a real README file
        if(strlen($this->getReadme()) > 300) {
            $score += 5;
        }

        // Medium boost for repos that uses travis ci
        if($this->usesTravisCi) {
            $score += 20;
        }
        
        $this->setScore($score);
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
     * Get the GitHub url of this repo
     *
     * @return string
     */
    public function getGitHubUrl()
    {
        return sprintf('http://github.com/%s/%s', $this->getUsername(), $this->getName());
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
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return null
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
     * @return null
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the repo creation date
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the repo creation date
     *
     * @return null
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the repo update date
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
     * Set contributors
     *
     * @param  array
     * @return null
     */
    public function setContributors(array $contributors)
    {
      $this->contributors = new ArrayCollection($contributors);
    }

    public function getContributorNames()
    {
        $names = array();
        foreach ($this->getContributors() as $contributor)
        {
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
}
