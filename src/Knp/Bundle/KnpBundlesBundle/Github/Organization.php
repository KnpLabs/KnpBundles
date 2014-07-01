<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\Exception\RuntimeException;

use Knp\Bundle\KnpBundlesBundle\Entity\Organization as EntityOrganization;
use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;

class Organization extends Owner
{
    /**
     * @var OwnerManager
     */
    private $manager;

    /**
     * @param OwnerManager $manager
     */
    public function setOwnerManager(OwnerManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function import($name, $update = true, $updateMembers = true)
    {
        $organization = new EntityOrganization();
        $organization->setName($name);

        if ($update && !$this->update($organization, $updateMembers)) {
            return false;
        }

        return $organization;
    }

    /**
     * @param EntityOrganization $organization
     *
     * @return boolean
     */
    public function update(EntityOrganization $organization, $updateMembers = true)
    {
        $keywords = array(
            $organization->getName()
        );
        if (null !== $organization->getFullName()) {
            $keywords[] = $organization->getFullName();
        }
        if (null !== $organization->getEmail()) {
            $keywords[] = $organization->getEmail();
        }

        /**
         * @var $api \Github\Api\Organization
         */
        $api = $this->getGithubClient()->api('organization');
        try {
            $data = $api->show($organization->getName());
        } catch(RuntimeException $e) {
            // Api limit ? Organization has been not found ?
            return false;
        }

        $this->updateOwner($organization, $data);

        if ($updateMembers) {
            try {
                $membersData = $api->members()->all($organization->getName());

                $organization->setMembers($this->updateMembers($membersData));
            } catch(RuntimeException $e) {
                // Api limit ? Can't access members info ? Skip it for now then.
            }
        }

        return true;
    }

    private function updateMembers($membersData)
    {
        $members = array();
        foreach ($membersData as $memberData) {
            if ($member = $this->manager->createOwner($memberData['login'], 'developer')) {
                $members[] = $member;
            }
        }

        return $members;
    }
}
