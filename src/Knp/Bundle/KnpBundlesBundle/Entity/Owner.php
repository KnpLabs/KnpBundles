<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\OwnerRepository")
 * @ORM\Table(
 *      name="owner",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="name_unique",columns={"name"})}
 * )
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator", type="string")
 * @ORM\DiscriminatorMap({"developer" = "Developer", "organization" = "Organization"})
 */
class Owner
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * User name, e.g. "ornicar"
     * Like in GitHub, this name is unique
     *
     * @Assert\NotBlank()
     * @Assert\Length(min = 2)
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $name;

    /**
     * Full name of the user, like "Thibault Duplessis"
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * User email
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $avatarUrl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $url;

    /**
     * The user location
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $location;

    /**
     * User creation date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * Internal score of the User as the sum of his bundles' scores
     *
     * @ORM\Column(type="integer")
     */
    protected $score = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $githubId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $sensioId;

    /**
     * Bundles the user owns
     *
     * @ORM\OneToMany(targetEntity="Bundle", mappedBy="owner")
     */
    protected $bundles;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * Set fullName
     *
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set email
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set avatarUrl
     *
     * @param string $avatarUrl
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * Get avatarUrl
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set location
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set score
     *
     * @param integer $score
     */
    public function setScore($score)
    {
        $this->score = $score;
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
     * @param string $githubId
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }

    /**
     * @return mixed
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * @param string $sensioId
     */
    public function setSensioId($sensioId)
    {
        $this->sensioId = $sensioId;
    }

    /**
     * @return mixed
     */
    public function getSensioId()
    {
        return $this->sensioId;
    }

    /**
     * Add bundles
     *
     * @param Bundle $bundle
     */
    public function addBundle(Bundle $bundle)
    {
        $this->bundles[] = $bundle;

        $bundle->setOwner($this);
    }

    /**
     * Remove bundles
     *
     * @param Bundle $bundle
     */
    public function removeBundle(Bundle $bundle)
    {
        $this->bundles->removeElement($bundle);
    }

    /**
     * Get bundles
     *
     * @return ArrayCollection
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * Count the user bundles
     *
     * @return integer
     */
    public function getNbBundles()
    {
        return $this->bundles->count();
    }

    /**
     * @return boolean
     */
    public function hasBundles()
    {
        return ($this->getNbBundles() > 0);
    }

    /**
     * Owner profile url on GitHub
     *
     * @return string
     */
    public function getGithubUrl()
    {
        return sprintf('http://github.com/%s', $this->name);
    }

    /**
     * Get the names of this user bundles
     *
     * @return array
     */
    public function getBundleNames()
    {
        $names = array();
        foreach ($this->bundles as $bundle) {
            $names[] = $bundle->getName();
        }

        return $names;
    }

    /**
     * Common method to fill in entity from array
     * @todo move it to another place
     */
    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{'set'.$key}($value);
        }
    }
}
