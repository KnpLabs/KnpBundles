<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Knp\Bundle\KnpBundlesBundle\Entity\Organization as EntityOrganization,
    Knp\Bundle\KnpBundlesBundle\Repository\OrganizationRepository;

use Github\HttpClient\ApiLimitExceedException;
use Symfony\Component\Console\Output\NullOutput;

class Organization extends Owner
{
    /**
     * @var OrganizationRepository
     */
    private $repository;

    /**
     * @param OrganizationRepository $repository
     */
    public function setRepository(OrganizationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string
     *
     * @return boolean|EntityOrganization
     */
    public function import($response)
    {
        $organization = new EntityOrganization();
        $organization->setName($response);

        if (!$this->update($organization)) {
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
        if (empty($data)) {
            return false;
        }

        $organization->setFullName(isset($data['fullname']) ? $data['fullname'] : null);
        $organization->setEmail(isset($data['email']) ? $data['email'] : null);
        $organization->setAvatarUrl(isset($data['avatar_url']) ? $data['avatar_url'] : null);
        $organization->setLocation(isset($data['location']) ? $data['location'] : null);
        $organization->setUrl(isset($data['url']) ? $this->fixUrl($data['url']) : null);

        $membersData = $api->members()->all($organization->getName());

        if (!$members = $this->updateMembers($membersData)) {
            return false;
        }

        $organization->setMembers($members);

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
