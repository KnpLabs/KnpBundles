<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\Exception\RuntimeException;

use Knp\Bundle\KnpBundlesBundle\Entity\Organization as EntityOrganization;
use Knp\Bundle\KnpBundlesBundle\Manager\OwnerManager;

class Organization extends Owner
{
    /**
     * Register organizations to avoid double
     *
     * @var array of strings
     */
    static private $registeredOrganizations = array();

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
    public function import($name, $update = true)
    {
        $organization = new EntityOrganization();
        $organization->setName($name);

        if ($update && !$this->update($organization)) {
            return false;
        }

        return $this->checkIfRegister($organization);
    }

    /**
     * @param EntityOrganization $organization
     *
     * @return boolean
     */
    public function update(EntityOrganization $organization)
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

        try {
            $membersData = $api->members()->all($organization->getName());

            $organization->setMembers($this->updateMembers($membersData));
        } catch(RuntimeException $e) {
            // Api limit ? Can't access members info ? Skip it for now then.
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

    private function checkIfRegister(EntityOrganization $organization)
    {
        foreach (self::$registeredOrganizations as $registeredOrganization) {
            /** @var string $registeredOrganization  */
            if ($organization->getName() === $registeredOrganization) {
                return true;
            }
        }
        self::$registeredOrganizations[] = $organization->getName();

        return $organization;
    }
}
