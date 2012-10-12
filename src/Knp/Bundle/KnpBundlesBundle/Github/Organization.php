<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\HttpClient\ApiLimitExceedException;
use Symfony\Component\Console\Output\NullOutput;

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
    public function import($name, $update = true)
    {
        $organization = new EntityOrganization();
        $organization->setName($name);

        if ($update && !$this->update($organization)) {
            return false;
        }

        return $organization;
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
        $api  = $this->getGithubClient()->api('organization');
        $data = $api->show($organization->getName());

        // Organization has been removed / not found ?
        if (empty($data) || isset($data['message'])) {
            return false;
        }

        $this->updateOwner($organization, $data);

        $membersData = $api->members()->all($organization->getName());
        // Can't access members info ? Skip it for now then.
        if (empty($membersData) || isset($membersData['message'])) {
            return true;
        }

        $organization->setMembers($this->updateMembers($membersData));

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
