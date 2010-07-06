<?php

namespace Application\S2bBundle\Document;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Full name of the user, like "Thibault Duplessis"
     * @String
     */
    protected $fullName = null;

    /**
     * The user company name
     * @String
     */
    protected $company = null;

    /**
     * The user location
     * @String
     */
    protected $location = null;

    /**
     * The user blog url
     * @String
     */
    protected $blog = null;

    /**
     * Bundles the user owns
     * @ReferenceMany(targetDocument="Application\S2bBundle\Document\Bundle")
     */
    protected $bundles = null;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
    }

    /**
     * Update a User Document from a GitHub user infos array 
     * 
     * @param array $user 
     */
    public function fromUserArray(array $user)
    {
        $this->setEmail(isset($user['email']) ? $user['email'] : null);
        $this->setFullName(isset($user['name']) ? $user['name'] : null);
        $this->setCompany(isset($user['company']) ? $user['company'] : null);
        $this->setLocation(isset($user['location']) ? $user['location'] : null);
        $this->setBlog(isset($user['blog']) ? $user['blog'] : null);
    }

    /**
     * Get blog
     * @return string
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set blog
     * @param  string
     * @return null
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
    }

    /**
     * Get location
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set location
     * @param  string
     * @return null
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get company
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set company
     * @param  string
     * @return null
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Get fullName
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set fullName
     * @param  string
     * @return null
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * Get bundles
     * @return Collection
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Count the user bundles
     *
     * @return int
     **/
    public function getNbBundles()
    {
        return $this->getBundles()->count();
    }

    /**
     * Add a bundle to this user bundles
     *
     * @return null
     **/
    public function addBundle(Bundle $bundle)
    {
        if($this->getBundles()->contains($bundle)) {
            throw new \OverflowException(sprintf('User %s already owns the %s bundle', $this->getName(), $bundle->getName()));
        }
        $this->getBundles()->add($bundle);
        $bundle->setUser($this);
    }

    /**
     * Get the date of the last commit
     * @return \DateTime
     **/
    public function getLastCommitAt()
    {
        $date = null;
        foreach($this->getBundles() as $bundle) {
            if(null === $date || $bundle->getLastCommitAt() > $date) {
                $date = $bundle->getLastCommitAt();
            }
        }

        return $date;
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
     * Get an obfuscated email, less likely to be deciphered by spambots
     *
     * @return string
     **/
    public function getObfuscatedEmail()
    {
        $text = $this->getEmail();
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            if (mt_rand(0, 1)) {
                $result .= substr($text, $i, 1);
            } else {
                if (mt_rand(0, 1)) {
                    $result .= '&#' . ord(substr($text, $i, 1)) . ';';
                } else {
                    $result .= '&#x' . sprintf("%x", ord(substr($text, $i, 1))) . ';';
                }
            }
        }
        if (mt_rand(0, 1)) {
            return str_replace('@', '&#64;', $result);
        } else {
            return str_replace('@', '&#x40;', $result);
        }
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
     * User url on GitHub
     *
     * @return string
     **/
    public function getGithubUrl()
    {
        return sprintf('http://github.com/%s', $this->getName());
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
