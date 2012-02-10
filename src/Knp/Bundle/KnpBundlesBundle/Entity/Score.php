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

    /**
     * Score detail based on the number of followers
     *
     * @ORM\Column(type="integer")
     */
    protected $followers;

    /**
     * Score detail based on the activity
     * (number of commits in the past 30 days)
     *
     * @ORM\Column(type="integer")
     */
    protected $activity;

    /**
     * Score detail based on how long the README file is (if any)
     *
     * @ORM\Column(type="integer")
     */
    protected $readme;

    /**
     * Score detail based on the bundle using TravisCI or not
     *
     * @ORM\Column(type="integer")
     */
    protected $travisci;
    
    /**
     * Score detail based on the result of the latest travis build
     * (passing or not passing)
     *
     * @ORM\Column(type="integer")
     */
    protected $travisbuild;
    
    /**
     * Score detail based on the bundle being installable using composer
     *
     * @ORM\Column(type="integer")
     */
    protected $composer;

    /**
     * Score detail based on the number of people who recommended this bundle
     *
     * @ORM\Column(type="integer")
     */
    protected $recommenders;

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

    public function setFollowers($followers)
    {
        $this->followers = $followers;
    }

    public function getFollowers()
    {
        return $this->followers;
    }

    public function setActivity($activity)
    {
        $this->activity = $activity;
    }

    public function getActivity()
    {
        return $this->activity;
    }

    public function setReadme($readme)
    {
        $this->readme = $readme;
    }

    public function getReadme()
    {
        return $this->readme;
    }

    public function setTravisci($travisci)
    {
        $this->travisci = $travisci;
    }

    public function getTravisci()
    {
        return $this->travisci;
    }

    public function setTravisbuild($travisbuild)
    {
        $this->travisbuild = $travisbuild;
    }

    public function getTravisbuild()
    {
        return $this->travisbuild;
    }

    public function setComposer($composer)
    {
        $this->composer = $composer;
    }

    public function getComposer()
    {
        return $this->composer;
    }

    public function setRecommenders($recommenders)
    {
        $this->recommenders = $recommenders;
    }

    public function getRecommenders()
    {
        return $this->recommenders;
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
