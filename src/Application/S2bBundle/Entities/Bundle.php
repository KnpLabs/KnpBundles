<?php

namespace Application\S2bBundle\Entities;
use Symfony\Components\Validator\Constraints;
use Symfony\Components\Validator\Mapping\ClassMetadata;

/**
 * An Open Source Bundle living on GitHub
 *
 * @Entity(repositoryClass="Application\S2bBundle\Entities\BundleRepository")
 * @Table(name="bundle")
 * @HasLifecycleCallbacks
 */
class Bundle
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('name', new Constraints\MinLength(2));
        $metadata->addPropertyConstraint('username', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('username', new Constraints\MinLength(2));
    }
    
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Bundle name, e.g. "MarkdownBundle"
     * Like in GitHub, this name is not unique
     *
     * @Column(type="string", length=127)
     */
    protected $name = null;

    /**
     * The name of the user who owns this bundle
     * This value is redundant with the name of the referenced User, for performance reasons
     *
     * @Column(type="string", length=127)
     */
    protected $username = null;

    /**
     * User who owns the bundle
     *
     * @ManyToOne(targetEntity="User", inversedBy="bundles")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user = null;

    /**
     * Bundle description
     *
     * @Column(type="string", length=255)
     */
    protected $description = null;

    /**
     * The bundle readme text extracted from source code
     *
     * @Column(type="text", nullable=true)
     */
    protected $readme = null;

    /**
     * Internal score of the Bundle, based on several indicators
     * Defines the Bundle position in lists and searches
     *
     * @Column(type="integer")
     */
    protected $score = null;

    /**
     * Bundle creation date (on this website)
     *
     * @Column(type="date")
     */
    protected $createdAt = null;

    /**
     * Bundle update date (on this website)
     *
     * @Column(type="date")
     */
    protected $updatedAt = null;

    /**
     * Date of the last Git commit
     *
     * @Column(type="date")
     */
    protected $lastCommitAt = null;

    /**
     * The last commits on this bundle repo
     *
     * @Column(type="text")
     */
    protected $lastCommits = null;

    /**
     * Released tags are Git tags
     * @Column(type="text")
     */
    protected $tags = null;

    /**
     * Number of GitHub followers
     * @Column(type="integer")
     */
    protected $nbFollowers = null;

    /**
     * Number of GitHub forks
     * @Column(type="integer")
     */
    protected $nbForks = null;

    /**
     * True if the Bundle is a fork
     * @Column(type="boolean")
     */
    protected $isFork = null;

    /**
     * Whether the bundle is available on GitHub or not
     * @Column(type="boolean")
     */
    protected $isOnGithub = null;

    public function __construct($fullName = null)
    {
        if($fullName) {
            list($this->username, $this->name) = explode('/', $fullName);
        }
    }
    
    /**
     * Get isFork
     * @return bool
     */
    public function getIsFork()
    {
      return $this->isFork;
    }
    
    /**
     * Set isFork
     * @param  bool
     * @return null
     */
    public function setIsFork($isFork)
    {
      $this->isFork = $isFork;
    }

    /**
     * Get tags
     * @return array
     */
    public function getTags()
    {
        return unserialize($this->tags);
    }

    /**
     * Set tags
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
        if(empty($tags)) {
            return null;
        }

        return reset($tags);
    }

    /**
     * Get lastCommits
     * @return array
     */
    public function getLastCommits()
    {
        return unserialize($this->lastCommits);
    }

    /**
     * Set lastCommits
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
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set score
     * @param  int
     * @return null
     */
    public function setScore($score)
    {
        $this->score = (int) $score;
    }

    public function recalculateScore()
    {
        $score = $this->getNbFollowers();
        $score += 3 * $this->getNbForks();
        if($this->getDaysSinceLastCommit() < 30) {
            $score += (30 - $this->getDaysSinceLastCommit()) / 5;
        }
        if(strlen($this->getReadme()) > 500) {
            $score += 5;
        }
        $this->setScore($score);
    }

    /**
     * Get the date of the last commit
     * @return \DateTime
     */
    public function getLastCommitAt()
    {
        return $this->lastCommitAt;
    }

    /**
     * Set lastCommitAt
     * @param  \DateTime
     * @return null
     */
    public function setLastCommitAt(\DateTime $lastCommitAt)
    {
        $this->lastCommitAt = $lastCommitAt;
    }

    /**
     * Returns the number of days elapsed since the last commit on the master branch
     * @return int
     **/
    public function getDaysSinceLastCommit()
    {
        return date_create()->diff($this->getLastCommitAt())->format('%d');
    }

    /**
     * Get forks
     * @return int
     */
    public function getNbForks()
    {
        return $this->nbForks;
    }

    /**
     * Set forks
     * @param  int
     * @return null
     */
    public function setNbForks($nbForks)
    {
        $this->nbForks = $nbForks;
    }

    /**
     * Get followers
     * @return int
     */
    public function getNbFollowers()
    {
        return $this->nbFollowers;
    }

    /**
     * Set followers
     * @param  int
     * @return null
     */
    public function setNbFollowers($nbFollowers)
    {
        $this->nbFollowers = $nbFollowers;
    }

    /**
     * Get the GitHub url of this repo
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
     **/
    public function getGitUrl()
    {
        return sprintf('git://github.com/%s/%s.git', $this->getUsername(), $this->getName());
    }

    /**
     * Get full name, including username
     * @return string
     */
    public function getFullName()
    {
        return $this->getUsername().'/'.$this->name;
    }

    /**
     * Get the first part of the name, without Bundle
     * @return string
     */
    public function getShortName()
    {
        return preg_replace('/^(.+)Bundle$/', '$1', $this->getName());
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * @param  string
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username
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
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     * @param  string
     * @return null
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get isOnGithub
     * @return boolean
     */
    public function getIsOnGithub()
    {
        return $this->isOnGithub;
    }

    /**
     * Set isOnGithub
     * @param  boolean
     * @return null
     */
    public function setIsOnGithub($isOnGithub)
    {
        $this->isOnGithub = $isOnGithub;
    }

    /**
     * getCreatedAt 
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
     **/
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * getUpdatedAt 
     * 
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /** @PreUpdate */
    public function markAsUpdated()
    {
        $this->updatedAt = new \DateTime();
    }

    /** @PrePersist */
    public function markAsCreated()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get an array representing the Bundle
     *
     * @return array
     **/
    public function toBigArray()
    {
        return array(
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'description' => $this->getDescription(),
            'score' => $this->getScore(),
            'nbFollowers' => $this->getNbFollowers(),
            'nbForks' => $this->getNbForks(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'lastCommitAt' => $this->getLastCommitAt()->getTimestamp(),
            'tags' => $this->getTags(),
            'lastCommits' => $this->getLastCommits(),
            'readme' => $this->getReadme()
        );
    }

    public function toSmallArray()
    {
        return array(
            'name' => $this->getName(),
            'username' => $this->getUsername(),
            'description' => $this->getDescription(),
            'score' => $this->getScore(),
            'nbFollowers' => $this->getNbFollowers(),
            'nbForks' => $this->getNbForks(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'lastCommitAt' => $this->getLastCommitAt()->getTimestamp(),
            'tags' => $this->getTags()
        );
    }

    public function __toString()
    {
        return $this->getUsername().'/'.$this->getName();
    }
}
