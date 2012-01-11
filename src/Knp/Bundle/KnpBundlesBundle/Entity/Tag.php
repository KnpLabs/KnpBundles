<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Bundles tag entity
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\TagRepository")
 * @ORM\Table(name="tags")
 */
class Tag
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Tag name, for example "ecommerce"
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $name;

    /**
     * Slugged name
     *
     * @ORM\Column(type="string", length=127)
     */
    protected $sluggedName;

    /**
     * Bundles tagged with this tag
     *
     * @ORM\ManyToMany(targetEntity="Bundle", mappedBy="composerTags")
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        $this->setSluggedName(preg_replace('/[^a-z0-9_\s-]/', '', preg_replace("/[\s_]/", "-", strtolower(trim($name)))));
    }

    public function getSluggedName()
    {
        return $this->sluggedName;
    }

    public function setSluggedName($sluggedName)
    {
        $this->sluggedName = $sluggedName;
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