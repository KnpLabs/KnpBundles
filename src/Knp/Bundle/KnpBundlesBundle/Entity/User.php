<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A user living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\UserRepository")
 * @ORM\Table(
 *      name="user",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="name_unique",columns={"name"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Constraints\NotBlank());
        $metadata->addPropertyConstraint('name', new Constraints\MinLength(2));
    }

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
     * @ORM\Column(type="string", length=127)
     */
    protected $name = null;

    /**
     * User email
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email = null;

    /**
     * User email
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $gravatarHash = null;

    /**
     * Full name of the user, like "Thibault Duplessis"
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $fullName = null;

    /**
     * The user company name
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $company = null;

    /**
     * The user location
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $location = null;

    /**
     * The user blog url
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $blog = null;

    /**
     * User creation date (on this website)
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt = null;

    /**
     * Bundles the user owns
     *
     * @ORM\OneToMany(targetEntity="Bundle", mappedBy="user")
     */
    protected $bundles = null;

    /**
     * Bundles this User contributed to
     *
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="contributors")
     */
    protected $contributionBundles = null;

    /**
     * local cache, not persisted
     */
    protected $lastCommitsCache = null;

    /**
     * Internal score of the User as the sum of his bundles' scores
     *
     * @ORM\Column(type="integer")
     */
    protected $score = null;

    /**
    * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="recommenders")
    */
    protected $recommendedBundles;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
        $this->recommendedBundles = new ArrayCollection();
        $this->contributionBundles = new ArrayCollection();
        $this->createdAt = new \DateTime('NOW');
    }

    /**
     * Get the gravatar hash
     *
     * @return string
     */
    public function getGravatarHash()
    {
        return $this->gravatarHash;
    }

    /**
     * Set the gravatar hash
     *
     * @param string $gravatarHash
     */
    public function setGravatarHash($gravatarHash)
    {
        $this->gravatarHash = $gravatarHash;
    }

    /**
     * Get blog
     *
     * @return string
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * Set blog
     *
     * @param  string
     * @return null
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;
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
     * Set location
     *
     * @param  string
     * @return null
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set company
     *
     * @param  string
     * @return null
     */
    public function setCompany($company)
    {
        $this->company = $company;
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
     * Set fullName
     *
     * @param  string
     * @return null
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
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
     * @return null
     */
    public function setScore($score)
    {
        $this->score = (int) $score;
    }

    /**
     * Calculate the score of this user based on his bundles' scores
     */
    public function recalculateScore()
    {
        $score = 0;
        foreach($this->getBundles() as $bundle) {
            $score += $bundle->getScore();
        }

        $this->setScore($score);
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

    public function getAllBundles()
    {
        return array_merge($this->getBundles()->toArray(), $this->getContributionBundles()->toArray());
    }

    /**
     * Count the user bundles
     *
     * @return integer
     */
    public function getNbBundles()
    {
        return $this->getBundles()->count();
    }

    public function hasBundles()
    {
        return ($this->getNbBundles() > 0);
    }

    /**
     * Get the names of this user bundles
     *
     * @return array
     */
    public function getBundleNames()
    {
        $names = array();
        foreach ($this->getBundles() as $bundle) {
            $names[] = $bundle->getName();
        }

        return $names;
    }

    /**
     * Add a bundle to this user bundles
     *
     * @return null
     */
    public function addBundle(Bundle $bundle)
    {
        if ($this->getBundles()->contains($bundle)) {
            throw new \OverflowException(sprintf('User %s already owns the %s bundle', $this->getName(), $bundle->getName()));
        }
        $this->getBundles()->add($bundle);
        $bundle->setUser($this);
    }

    /**
     * Remove a bundle from this user bundles
     *
     * @return null
     */
    public function removeBundle(Bundle $bundle)
    {
        $this->getBundles()->removeElement($bundle);
        $bundle->setUser(null);
    }

    /**
     * Get contributionBundles
     *
     * @return ArrayCollection
     */
    public function getContributionBundles()
    {
        return $this->contributionBundles;
    }

    public function getContributionBundlesSortedByScore()
    {
        return $this->sortBundlesByScore($this->getContributionBundles()->toArray());
    }

    public function hasContributionBundles()
    {
        return !empty($this->contributionBundles);
    }

    /**
     * Set contributionBundles
     *
     * @param  ArrayCollection
     * @return null
     */
    public function setContributionBundles(ArrayCollection $contributionBundles)
    {
        $this->contributionBundles = $contributionBundles;
    }

    public function getNbContributionBundles()
    {
        return $this->getContributionBundles()->count();
    }

    protected function sortBundlesByScore(array $bundles)
    {
        uasort($bundles, function($a, $b) {
            return $a->getScore() > $b->getScore();
        });

        return $bundles;
    }

    /**
     * Get the date of the last commit
     *
     * @return \DateTime
     */
    public function getLastCommitAt()
    {
        $lastCommits = $this->getLastCommits(1);
        if (empty($lastCommits)) {
            return null;
        }
        $lastCommit = $lastCommits[0];
        $date = new \DateTime($lastCommit['committed_date']);

        return $date;
    }

    /**
     * Get the more recent commits by this user
     *
     * @return array
     */
    public function getLastCommits($nb = 10)
    {
        if (null === $this->lastCommitsCache) {
            $commits = array();
            foreach ($this->getAllBundles() as $bundle) {
                foreach ($bundle->getLastCommits() as $commit) {
                    if (isset($commit['author']['login']) && $commit['author']['login'] === $this->getName()) {
                        $commits[] = $commit;
                    }
                }
            }
            usort($commits, function($a, $b)
                    {
                        return strtotime($a['committed_date']) < strtotime($b['committed_date']);
                    });
            $this->lastCommitsCache = $commits;
        }
        $commits = array_slice($this->lastCommitsCache, 0, $nb);

        return $commits;
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
     * Get an obfuscated email, less likely to be deciphered by spambots
     *
     * @return string
     */
    public function getObfuscatedEmail()
    {
        $text = $this->getEmail();
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            if (mt_rand(0, 1)) {
                $result .= substr($text, $i, 1);
            } else {
                if (mt_rand(0, 1)) {
                    $result .= '&#'.ord(substr($text, $i, 1)).';';
                } else {
                    $result .= '&#x'.sprintf("%x", ord(substr($text, $i, 1))).';';
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
     *
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
     */
    public function getGithubUrl()
    {
        return sprintf('http://github.com/%s', $this->getName());
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
     * Get name
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->getName();
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
     * getCreatedAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the user creation date
     *
     * @return null
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /** @ORM\PrePersist */
    public function markAsCreated()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function toSmallArray()
    {
        return array(
            'name'          => $this->getName(),
            'email'         => $this->getEmail(),
            'gravatarHash'  => $this->getGravatarHash(),
            'fullName'      => $this->getFullName(),
            'company'       => $this->getCompany(),
            'location'      => $this->getLocation(),
            'blog'          => $this->getBlog(),
            'bundles'       => $this->getBundleNames(),
            'lastCommitAt'  => $this->getLastCommitAt() ? $this->getLastCommitAt()->getTimestamp() : null,
            'score'         => $this->getScore(),
        );
    }

    public function toBigArray()
    {
        return $this->toSmallArray() + array(
            'lastCommits' => $this->getLastCommits()
        );
    }

    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $this->{'set'.$key}($value);
        }
    }

    /* ---------- Security User ---------- */

    public function getRoles()
    {
        return array('ROLE_USER');
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function eraseCredentials()
    {
    }

    public function equals(UserInterface $user)
    {
        return $user instanceof User && $user->getUsername() === $this->getUsername();
    }

    public function getUsedBundles()
    {
        return $this->recommendedBundles;
    }

    public function isUsingBundle(Bundle $bundle)
    {
        return $this->getUsedBundles()->contains($bundle);
    }

    public function addRecommendedBundle(Bundle $bundle)
    {
        $this->recommendedBundles[] = $bundle;
    }
}
