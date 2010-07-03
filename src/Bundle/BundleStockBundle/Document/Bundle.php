<?php

namespace Bundle\BundleStockBundle\Document;

/**
 * An Open Source Bundle living on GitHub
 *
 * @Document(db="symfony2bundles", collection="bundle")
 * @HasLifecycleCallbacks
 */
class Bundle
{
    /**
     * Bundle name, e.g. "MarkdownBundle"
     * Like in GitHub, this name is not unique
     *
     * @String
     * @Validation({ @NotBlank, @Regex("/^\w+Bundle$/") })
     * @var string
     */
    protected $name = null;

    /**
     * Username, e.g. "knplabs"
     *
     * @String
     * @Validation({ @NotBlank, @Regex("/^\w+$/") })
     * @var string
     */
    protected $username = null;

    /**
     * Bundle description
     *
     * @String
     * @var string
     */
    protected $description = null;

    /**
     * Internal ranking of the Bundle, based on several indicators
     * Defines the Bundle position in lists and searches
     *
     * @Float
     * @var float
     */
    protected $rank = null;

    /**
     * @Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * Wheter the bundle is available on GitHub or not
     *
     * @Boolean
     * @Column(name="is_on_github", type="boolean")
     * @var boolean
     */
    protected $isOnGithub = null;

    /**
     * Primary key
     *
     * @var string
     */
    protected $id = null;

    /**
     * Updates a Bundle Document from a repository infos array 
     * 
     * @param array $rep 
     */
    public function fromRepositoryArray(array $repo)
    {
        $this->setName($repo['name']);
        $this->setUsername($repo['username']);
        $this->setDescription($repo['description']);
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
     * Get full name, including username
     * @return string
     */
    public function getFullName()
    {
        return $this->username.'/'.$this->name;
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
     * Get id
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
        $this->createdAt= new \DateTime();
    }

    public function __toString()
    {
        return $this->getUsername().'/'.$this->getName();
    }
}
