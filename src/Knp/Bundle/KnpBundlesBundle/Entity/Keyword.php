<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
}
