<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A score of a given repo at a given date
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Entity\ScoreRepository")
 * @ORM\Table(
 *      name="score",
 *      indexes={
 *          @ORM\Index(name="date", columns={"date"}),
 *          @ORM\Index(name="repo", columns={"repo_id"}),
 *      },
 *      uniqueConstraints={@ORM\UniqueConstraint(name="date_repo",columns={"date", "repo_id"})}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Score
{

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Date of the score snapshot
     *
     * @ORM\Column(type="date")
     */
    protected $date = null;

    /**
     * Repo
     *
     * @ORM\ManyToOne(targetEntity="Repo", inversedBy="scores")
     * @ORM\JoinColumn(name="repo_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $repo = null;

    /**
     * Internal value of the Repo, based on several indicators
     * Defines the Repo position in lists and searches
     *
     * @ORM\Column(type="integer")
     */
    protected $value = null;


    public function __construct()
    {
        $this->repo = null;
        $this->date = new \DateTime();
        $this->value = 0;
    }


    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     * @param  integer
     * @return null
     */
    public function setValue($value)
    {
        $this->value = (int) $value;
    }

    /**
     * Get the date of the last commit
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param  \DateTime
     * @return null
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return Repo
     */
    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * @param Repo $repo
     */
    public function setRepo(Repo $repo = null)
    {
        $this->repo = $repo;
    }

    public function __toString()
    {
        return $this->value;
    }
}
