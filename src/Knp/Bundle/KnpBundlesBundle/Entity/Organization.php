<?php

namespace Knp\Bundle\KnpBundlesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * An organization living on GitHub
 *
 * @ORM\Entity(repositoryClass="Knp\Bundle\KnpBundlesBundle\Repository\OrganizationRepository")
 */
class Organization extends Owner
{
    /**
     * Members of this organization
     *
     * @ORM\ManyToMany(targetEntity="Developer", inversedBy="organizations", cascade={"persist"})
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();

        parent::__construct();
    }

    /**
     * Add members
     *
     * @param Developer $member
     */
    public function addMember(Developer $member)
    {
        $this->members[] = $member;
    }

    /**
     * Remove members
     *
     * @param Developer $member
     */
    public function removeMember(Developer $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members
     *
     * @return ArrayCollection
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Set members
     * @param ArrayCollection|array $members
     */
    public function setMembers($members)
    {
        if ($members instanceof ArrayCollection) {
            $this->members = $members;
        } elseif (is_array($members)) {
            foreach ($members as $member) {
                if (!$this->members->contains($member)) {
                    $this->addMember($member);
                }
            }
        }
    }

    /**
     * Get the names of this organization members
     *
     * @return array
     */
    public function getMemberNames()
    {
        $names = array();
        foreach ($this->members as $member) {
            $names[] = $member->getName();
        }

        return $names;
    }

    public function toArray()
    {
        return array(
            'name'          => $this->getName(),
            'email'         => $this->getEmail(),
            'avatarUrl'     => $this->getAvatarUrl(),
            'fullName'      => $this->getFullName(),
            'location'      => $this->getLocation(),
            'blog'          => $this->getUrl(),
            'bundles'       => $this->getBundleNames(),
            'members'       => $this->getMemberNames(),
            'score'         => $this->getScore(),
        );
    }
}
