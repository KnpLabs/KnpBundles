<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A score of a given bundle at a given date
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\ScoreRepository")
 * @ORM\Table(
 *      name="score",
 *      indexes={
 *          @ORM\Index(name="date", columns={"date"}),
 *          @ORM\Index(name="bundle", columns={"bundle_id"}),
 *      },
 *      uniqueConstraints={@ORM\UniqueConstraint(name="date_bundle",columns={"date", "bundle_id"})}
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
     * Bundle
     *
     * @ORM\ManyToOne(targetEntity="Bundle", inversedBy="scores")
     * @ORM\JoinColumn(name="bundle_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $bundle = null;

    /**
     * Internal value of the Bundle, based on several indicators
     * Defines the Bundle position in lists and searches
     *
     * @ORM\Column(type="integer")
     */
    protected $value = null;

    public function __construct()
    {
        $this->bundle = null;
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
     *
     * @param  integer
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
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param Bundle $bundle
     */
    public function setBundle(Bundle $bundle = null)
    {
        $this->bundle = $bundle;
    }

    public function __toString()
    {
        return $this->value;
    }
}
