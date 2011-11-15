<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * A link to repo manuals and how-to
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Entity\LinkRepository")
 * @ORM\Table(
 *      name="link"
 * )
 * @ORM\HasLifecycleCallbacks
 */
class Link
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('url', new Constraints\NotBlank());
    }

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Link title
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title = null;

    /**
     * Link url
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $url = null;


    /**
     * Repo the link is for
     *
     * @ORM\ManyToOne(targetEntity="Repo", inversedBy="links")
     * @ORM\JoinColumn(name="repo_id", referencedColumnName="id", nullable=false)
     */
    protected $repo = null;

    public function __construct($url, $title = null)
    {
        $this->setUrl($url);
        if (!is_null($title)) {
            $this->setTitle($title);
        }
    }

    /**
     * Get the link title
     *
     * @return string
     */
    public function getTitle()
    {
        return !is_null($this->title) ? $this->title : $this->getUrl();
    }

    /**
    * Set link title
    *
    * @param  string
    * @return null
    */
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    /**
    * Get the link url
    *
    * @return string
    */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Set link url
     *
     * @param  string
     * @return null
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }
    
    /**
    * Get related repo
    *
    * @return Repo
    */
    public function getRepo()
    {
        return $this->repo;
    }
    
    /**
     * Set repo
     *
     * @param  Repo
     * @return null
     */
    public function setRepo(Repo $repo)
    {
        $this->repo = $repo;
    }
}
