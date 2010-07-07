<?php

namespace Application\S2bBundle\Document;

/**
 * An Open Source Bundle living on GitHub
 *
 * @Document(
 *   db="symfony2bundles",
 *   collection="bundle",
 *   indexes={
 *     @Index(keys={"username"="asc", "name"="asc"}, options={"unique"=true})
 *   }
 * )
 * @HasLifecycleCallbacks
 */
class Bundle
{
    /**
     * Bundle name, e.g. "MarkdownBundle"
     * Like in GitHub, this name is not unique
     * @String
     * @Validation({ @NotBlank, @Regex("/^\w+Bundle$/") })
     */
    protected $name = null;

    /**
     * The name of the user who owns this bundle
     * This value is redundant with the name of the referenced User, for performance reasons
     * @String
     * @Validation({ @NotBlank })
     */
    protected $username = null;

    /**
     * User who owns the bundle
     * @ReferenceOne(targetDocument="Application\S2bBundle\Document\User")
     * @Validation({ @NotBlank, @Valid })
     */
    protected $user = null;

    /**
     * Bundle description
     * @String
     */
    protected $description = null;

    /**
     * The bundle readme text extracted from source code
     * @String
     */
    protected $readme = null;

    /**
     * Internal score of the Bundle, based on several indicators
     * Defines the Bundle position in lists and searches
     * @Float
     */
    protected $score = null;

    /**
     * Bundle creation date (on this website)
     * @Date
     */
    protected $createdAt;

    /**
     * Bundle update date (on this website)
     * @Date
     */
    protected $updatedAt;

    /**
     * Date of the last Git commit
     * @Date
     */
    protected $lastCommitAt = null;

    /**
     * The last commits on this bundle repo
     * @Field(type="collection")
     */
    protected $lastCommits = array();

    /**
     * Released tags are Git tags
     * @Field(type="collection")
     */
    protected $tags = array();

    /**
     * Whether the bundle is available on GitHub or not
     * @Boolean
     * @Validation({@AssertType("boolean")})
     */
    protected $isOnGithub = null;

    /**
     * Number of GitHub followers
     * @Int
     */
    protected $followers = null;

    /**
     * Number of GitHub forks
     * @Int
     */
    protected $forks = null;

    /**
     * Updates a Bundle Document from a repository infos array 
     * 
     * @param array $rep 
     */
    public function fromRepositoryArray(array $repo)
    {
        $this->setName($repo['name']);
        $this->setUsername(isset($repo['username']) ? $repo['username'] : $repo['owner']);
        $this->setDescription($repo['description']);
        $this->setFollowers(isset($repo['followers']) ? $repo['followers'] : $repo['watchers']);
        $this->setForks($repo['forks']);
        $this->setCreatedAt(new \DateTime(isset($repo['created']) ? $repo['created'] : $repo['created_at']));
    }

    /**
     * Get tags
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set tags
     * @param  array
     * @return null
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }

    public function getLastTagName()
    {
        if(empty($this->tags)) {
            return null;
        }

        return reset($this->tags);
    }

    /**
     * Get lastCommits
     * @return array
     */
    public function getLastCommits()
    {
        return $this->lastCommits;
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
        $this->lastCommits = $lastCommits;

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
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    public function getRoundScore()
    {
        return round($this->score);
    }

    /**
     * Set score
     * @param  float
     * @return null
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    public function recalculateScore()
    {
        $score = $this->getFollowers();
        $score += 3 * $this->getForks();
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
    public function getForks()
    {
        return $this->forks;
    }

    /**
     * Set forks
     * @param  int
     * @return null
     */
    public function setForks($forks)
    {
        $this->forks = $forks;
    }

    /**
     * Get followers
     * @return int
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * Set followers
     * @param  int
     * @return null
     */
    public function setFollowers($followers)
    {
        $this->followers = $followers;
    }

    /**
     * Get rank
     * @return float
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set rank
     * @param  float
     * @return null
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
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
     * Get the author url on GitHub
     *
     * @return string
     **/
    public function getUsernameUrl()
    {
        return sprintf('http://github.com/%s', $this->getUsername());
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

    public function __toString()
    {
        return $this->getUsername().'/'.$this->getName();
    }
}
