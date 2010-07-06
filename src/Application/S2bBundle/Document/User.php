<?php

namespace Application\S2bBundle\Document;

/**
 * An user living on GitHub
 *
 * @Document(
 *   db="symfony2bundles",
 *   collection="user",
 *   indexes={
 *     @Index(keys={"name"="asc"}, options={"unique"=true})
 *   }
 * )
 * @HasLifecycleCallbacks
 */
class User
{
    /**
     * User name, e.g. "ornicar"
     * Like in GitHub, this name is unique
     * @String
     * @Validation({ @NotBlank })
     */
    protected $name = null;

    /**
     * User email
     * @String
     */
    protected $email = null;

    /**
     * Updates a Bundle Document from a repository infos array 
     * @param array $user 
     */
    public function fromUserArray(array $user)
    {
        $this->setName($user['name']);
        $this->setEmail($user['email']);
    }
    
    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
      return $this->email;
    }
    
    /**
     * Set email
     * @param  string
     * @return null
     */
    public function setEmail($email)
    {
      $this->email = $email;
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

    public function __toString()
    {
        return $this->getName();
    }
}
