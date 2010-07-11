<?php

namespace Application\S2bBundle\Entities;
use Symfony\Components\Validator\Constraints;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Components\Validator\Mapping\ClassMetadata;

/**
 * A user living on GitHub
 *
 * @Entity(repositoryClass="Application\S2bBundle\Entities\UserRepository")
 * @Table(name="user")
 * @HasLifecycleCallbacks
 */
class User
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('name', new Constraints\MinLength(2));
    }
    
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * User name, e.g. "ornicar"
     * Like in GitHub, this name is unique
     *
     * @Column(type="string", length=127)
     */
    protected $name = null;

    /**
     * User email
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $email = null;

    /**
     * Full name of the user, like "Thibault Duplessis"
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $fullName = null;

    /**
     * The user company name
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $company = null;

    /**
     * The user location
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $location = null;

    /**
     * The user blog url
     *
     * @Column(type="string", length=255, nullable=true)
     */
    protected $blog = null;

    /**
     * Bundles the user owns
     *
     * @OneToMany(targetEntity="Bundle", mappedBy="user")
     */
    protected $bundles = null;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
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
     * @return ArrayCollection
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
     * Get the names of this user bundles
     *
     * @return array
     **/
    public function getBundleNames()
    {
        $names = array();
        foreach($this->getBundles() as $bundle) {
            $names[] = $bundle->getName();
        }

        return $names;
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
     * Remove a bundle from this user bundles
     *
     * @return null
     **/
    public function removeBundle(Bundle $bundle)
    {
        $this->getBundles()->removeElement($bundle);
        $bundle->setUser(null);
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
     * Get the more recent commits by this user
     *
     * @return array
     **/
    public function getLastCommits()
    {
        $commits = array();
        foreach($this->getBundles() as $bundle) {
            $commits = array_merge($commits, $bundle->getLastCommits());
        }
        usort($commits, function($a, $b)
        {
            return strtotime($a['committed_date']) < strtotime($b['committed_date']);
        });
        $commits = array_slice($commits, 0, 10);

        return $commits;
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
