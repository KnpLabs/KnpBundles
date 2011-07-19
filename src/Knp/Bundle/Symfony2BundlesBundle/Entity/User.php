<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * A user living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\Symfony2BundlesBundle\Entity\UserRepository")
 * @ORM\Table(
 *      name="user",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="name_unique",columns={"name"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class User
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
     * Repos the user owns
     *
     * @ORM\OneToMany(targetEntity="Repo", mappedBy="user")
     */
    protected $repos = null;

    /**
     * Repos this User contributed to
     *
     * @ORM\ManyToMany(targetEntity="Repo", mappedBy="contributors")
     */
    protected $contributionRepos = null;

    /**
     * local cache, not persisted
     */
    protected $lastCommitsCache = null;

    /**
     * Internal score of the User as the sum of his repos' scores
     *
     * @ORM\Column(type="integer")
     */
    protected $score = null;

    public function __construct()
    {
        $this->repos = new ArrayCollection();
        $this->contributionRepos = new ArrayCollection();
        $this->createdAt = new \DateTime('NOW');
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
     * Calculate the score of this user based on his repos' scores
     */
    public function recalculateScore()
    {
        $score = 0;
        foreach($this->getRepos() as $repo) {
            $score += $repo->getScore();
        }

        $this->setScore($score);
    }

    /**
     * Get repos
     *
     * @return ArrayCollection
     */
    public function getRepos()
    {
        return $this->repos;
    }

    public function getAllRepos()
    {
        return array_merge($this->getRepos()->toArray(), $this->getContributionRepos()->toArray());
    }

    public function getBundles()
    {
        $bundles = array();
        foreach ($this->getRepos() as $repo) {
            if ($repo instanceof Bundle) {
                $bundles[] = $repo;
            }
        }

        return $bundles;
    }

    public function getBundleNames()
    {
        $names = array();
        foreach ($this->getBundles() as $bundle) {
            $names[] = $bundle->getName();
        }

        return $names;
    }

    public function getProjects()
    {
        $projects = array();
        foreach ($this->getRepos() as $repo) {
            if ($repo instanceof Project) {
                $projects[] = $repo;
            }
        }

        return $projects;
    }

    public function getProjectNames()
    {
        $names = array();
        foreach ($this->getProjects() as $project) {
            $names[] = $project->getName();
        }

        return $names;
    }


    /**
     * Count the user repos
     *
     * @return integer
     */
    public function getNbRepos()
    {
        return $this->getRepos()->count();
    }

    public function getNbBundles()
    {
        return count($this->getBundles());
    }

    public function getNbProjects()
    {
        return count($this->getProjects());
    }

    public function hasProjects()
    {
        return ($this->getNbProjects() > 0);
    }


    public function hasBundles()
    {
        return ($this->getNbBundles() > 0);
    }

    /**
     * Get the names of this user repos
     *
     * @return array
     */
    public function getRepoNames()
    {
        $names = array();
        foreach ($this->getRepos() as $repo) {
            $names[] = $repo->getName();
        }

        return $names;
    }

    /**
     * Add a repo to this user repos
     *
     * @return null
     */
    public function addRepo(Repo $repo)
    {
        if ($this->getRepos()->contains($repo)) {
            throw new \OverflowException(sprintf('User %s already owns the %s repo', $this->getName(), $repo->getName()));
        }
        $this->getRepos()->add($repo);
        $repo->setUser($this);
    }

    /**
     * Remove a repo from this user repos
     *
     * @return null
     */
    public function removeRepo(Repo $repo)
    {
        $this->getRepos()->removeElement($repo);
        $repo->setUser(null);
    }

    /**
     * Get contributionRepos
     *
     * @return ArrayCollection
     */
    public function getContributionRepos()
    {
        return $this->contributionRepos;
    }

    public function getContributionReposSortedByScore()
    {
        return $this->sortReposByScore($this->getContributionRepos()->toArray());
    }

    public function hasContributionRepos()
    {
        return !empty($this->contributionRepos);
    }

    public function getContributionBundles()
    {
        $bundles = array();
        foreach ($this->getContributionReposSortedByScore() as $repo) {
            if ($repo instanceof Bundle) {
                $bundles[] = $repo;
            }
        }

        return $bundles;
    }

    public function getNbContributionBundles()
    {
        return count($this->getContributionBundles());
    }

    public function getContributionProjects()
    {
        $projects = array();
        foreach ($this->getContributionReposSortedByScore() as $repo) {
            if ($repo instanceof Project) {
                $projects[] = $repo;
            }
        }

        return $projects;
    }

    public function getNbContributionProjects()
    {
        return count($this->getContributionProjects());
    }

    /**
     * Set contributionRepos
     *
     * @param  ArrayCollection
     * @return null
     */
    public function setContributionRepos(ArrayCollection $contributionRepos)
    {
        $this->contributionRepos = $contributionRepos;
    }

    public function getNbContributionRepos()
    {
        return $this->getContributionRepos()->count();
    }

    protected function sortReposByScore(array $repos)
    {
        uasort($repos, function($a, $b) {
            return $a->getScore() > $b->getScore();
        });

        return $repos;
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
            foreach ($this->getAllRepos() as $repo) {
                foreach ($repo->getLastCommits() as $commit) {
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
            'fullName'      => $this->getFullName(),
            'company'       => $this->getCompany(),
            'location'      => $this->getLocation(),
            'blog'          => $this->getBlog(),
            'bundles'       => $this->getBundleNames(),
            'projects'      => $this->getProjectNames(),
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
}
