<?php

namespace Bundle\BundleStockBundle\Document;

/**
 * An Open Source Bundle living on GitHub
 *
 * @Document(db="symfony2bundles", collection="bundle")
 */
class Bundle
{
    /**
    * Bundle name, e.g. "MarkdownBundle"
    * Like in GitHub, this name is not unique
     *
     * @String
     * @Validation({
     *   @NotBlank,
     *   @MinLength(11)
     * })
     * @var string
     */
    protected $name = null;

    /**
     * Author name, e.g. "knplabs"
     *
     * @String
     * @var string
     */
    protected $author = null;

    /**
     * Internal ranking of the Bundle, based on several indicators
     * Defines the Bundle position in lists and searches
     *
     * @Float
     * @var float
     */
    protected $rank = null;
    
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
     * Primary key
     *
     * @var string
     */
    protected $id = null;

    /**
     * Get the GitHub url of this repo
     * @return string
     */
    public function getGitHubUrl()
    {
        return sprintf('http://github.com/%s/%s', $this->getAuthor(), $this->getName());
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
     * Get author
     * @return string
     */
    public function getAuthor()
    {
      return $this->author;
    }
    
    /**
     * Set author
     * @param  string
     * @return null
     */
    public function setAuthor($author)
    {
      $this->author = $author;
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
     * Set id
     * @param  string
     * @return null
     */
    public function setId($id)
    {
      $this->id = $id;
    }

    public function __toString()
    {
        return $this->getAuthor().'/'.$this->getName();
    }
}
