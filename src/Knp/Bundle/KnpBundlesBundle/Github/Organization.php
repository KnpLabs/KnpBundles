<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Knp\Bundle\KnpBundlesBundle\Entity\Organization as EntityOrganization;
use Knp\Bundle\KnpBundlesBundle\Repository\OwnerRepository;

use Github\HttpClient\ApiLimitExceedException;
use Symfony\Component\Console\Output\NullOutput;

class Organization extends Owner
{
    /**
     * @var OwnerRepository
     */
    private $repository;

    /**
     * @param OwnerRepository $repository
     */
    public function setRepository(OwnerRepository $repository)
    {
        $this->repository = $repository;
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
        if ($members = $this->updateMembers($membersData)) {
            $organization->setMembers($members);
        }

        return true;
    }

    private function updateMembers($membersData)
    {
        $members = array();
        $api = new Developer($this->github, new NullOutput());

        foreach ($membersData as $memberData) {
            if (!$member = $this->repository->findOneBy(array('name' => $memberData['login']))) {
                $member = $api->import($memberData['login']);
            }

            $members[] = $member;
        }

        return $members;
    }
}
