<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bundles keyword entity
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\KeywordRepository")
 * @ORM\Table(name="keyword")
 */
class Keyword
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Keyword value, for example "ecommerce"
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $value;

    /**
     * Keyword slug
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="keywords")
     */
    protected $bundles;

    public function __construct()
    {
        $this->bundles = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
        $this->setSlug(preg_replace('/[^a-z0-9_\s-]/', '', preg_replace("/[\s_]/", "-", strtolower(trim($value)))));
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return ArrayCollection
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    public function setBundles(ArrayCollection $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * @return int Total nb of bundles tagged with this tag
     */
    public function countBundles()
    {
        return count($this->bundles);
    }

    public function hasBundle(Bundle $bundle)
    {
        return $this->bundles->contains($bundle);
    }

    public function addBundle(Bundle $bundle)
    {
        if (!$this->hasBundle($bundle)) {
            $this->bundles[] = $bundle;
        }
    }

    public function removeBundle(Bundle $bundle)
    {
        if ($this->hasBundle($bundle)) {
            $this->bundles->removeElement($bundle);
        }
    }
}