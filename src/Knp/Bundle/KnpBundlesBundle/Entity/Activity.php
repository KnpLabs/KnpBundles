<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\ActivityRepository", readOnly=true)
 * @ORM\Table(
 *      name="activities",
 *      indexes={
 *          @ORM\Index(name="type_state", columns={"type", "state"}),
 *          @ORM\Index(name="created_at", columns={"createdAt"})
 *      }
 * )
 */
class Activity
{
    const ACTIVITY_TYPE_COMMIT       = 1;
    const ACTIVITY_TYPE_WATCH        = 2;
    const ACTIVITY_TYPE_STAR         = 3;
    const ACTIVITY_TYPE_ISSUE        = 5;
    const ACTIVITY_TYPE_PR           = 6;
    const ACTIVITY_TYPE_FORK         = 7;
    const ACTIVITY_TYPE_DOWNLOAD     = 8;

    const ACTIVITY_TYPE_TRAVIS_BUILD = 10;
    const ACTIVITY_TYPE_RECOMMEND    = 15;
//    const ACTIVITY_TYPE_FAVOR        = 16;

//    const ACTIVITY_TYPE_ASKED        = 20;
//    const ACTIVITY_TYPE_ANSWERED     = 21;
//    const ACTIVITY_TYPE_VOTED        = 22;

    const ACTIVITY_TYPE_TWEETED      = 30;

    const STATE_UNKNOWN = 0;
    const STATE_OPEN    = 1;
    const STATE_CLOSED  = 2;
    const STATE_FIXED   = 3;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var integer
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    private $state = self::STATE_UNKNOWN;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $author;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $bundleName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $bundleOwnerName;

    /**
     * Bundle
     *
     * @ORM\ManyToOne(targetEntity="Bundle", inversedBy="activities")
     *
     * @var Bundle
     */
    private $bundle;

    /**
     * Developer
     *
     * @ORM\ManyToOne(targetEntity="Developer", inversedBy="activities")
     *
     * @var Developer
     */
    private $developer;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = (int) $type;
    }

    /**
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param integer $state
     */
    public function setState($state)
    {
        $this->state = (int) $state;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return null|string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return null|string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @param string $bundleName
     */
    public function setBundleName($bundleName)
    {
        $this->bundleName = $bundleName;
    }

    /**
     * @return string
     */
    public function getBundleOwnerName()
    {
        return $this->bundleOwnerName;
    }

    /**
     * @param string $bundleOwnerName
     */
    public function setBundleOwnerName($bundleOwnerName)
    {
        $this->bundleOwnerName = $bundleOwnerName;
    }

    /**
     * @return null|Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param Bundle $bundle
     */
    public function setBundle(Bundle $bundle)
    {
        $this->bundle          = $bundle;
        $this->bundleName      = $bundle->getName();
        $this->bundleOwnerName = $bundle->getOwnerName();

        $bundle->addActivity($this);
    }

    /**
     * @return null|Developer
     */
    public function getDeveloper()
    {
        return $this->developer;
    }

    /**
     * @param Developer $developer
     */
    public function setDeveloper(Developer $developer)
    {
        $this->developer = $developer;
        $this->author    = $developer->getName();

        $developer->addActivity($this);
    }

    /**
     * @param Activity $activity
     *
     * @return boolean
     */
    public function isEqualTo(Activity $activity)
    {
        if ($activity->getBundle()->getId() !== $this->bundle->getId()) {
            return false;
        }

        // Compare developers only if actual activity has one
        if (null !== $this->developer && $activity->getDeveloper()->getId() !== $this->developer->getId()) {
            return false;
        }

        if ($activity->getType() !== $this->type) {
            return false;
        }

        if ($activity->getState() !== $this->state) {
            return false;
        }

        // Compare dates only when state of activity allows it
        if (self::STATE_FIXED !== $this->state && $activity->getCreatedAt()->getTimestamp() !== $this->createdAt->getTimestamp()) {
            return false;
        }

        return true;
    }
}
