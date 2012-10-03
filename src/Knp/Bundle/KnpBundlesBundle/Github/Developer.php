<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\HttpClient\ApiLimitExceedException;

use Knp\Bundle\KnpBundlesBundle\Entity\Developer as EntityDeveloper;

class Developer extends Owner
{
    /**
     * {@inheritDoc}
     */
    public function import($name, $update = true)
    {
        $developer = new EntityDeveloper();
        $developer->setName($name);

        if ($update && !$this->update($developer)) {
            return false;
        }

        return $developer;
    }

    /**
     * @param EntityDeveloper $developer
     *
     * @return boolean
     */
    public function update(EntityDeveloper $developer)
    {
        $keywords = array(
            $developer->getName()
        );
        if (null !== $developer->getFullName()) {
            $keywords[] = $developer->getFullName();
        }
        if (null !== $developer->getEmail()) {
            $keywords[] = $developer->getEmail();
        }

        $api  = $this->github->api('user');
        $data = $api->show($developer->getName());

        if (empty($data) || isset($data['message'])) {
            foreach ($keywords as $field) {
                try {
                    $data = $api->search($field);
                    if (isset($data['users']) && 0 < count($data['users'])) {
                        $data = $data['users'][0];
                        // Let's call API one more time to get clean user data
                        $data = $api->show($data['login']);
                    }
                } catch(ApiLimitExceedException $e) {
                    break;
                }

                // Did we found user in this iteration ?
                if (!empty($data) && !isset($data['message'])) {
                    break;
                }
            }
        }

        // Developer has been removed / not found ?
        if (empty($data) || isset($data['message'])) {
            return false;
        }

        $this->updateOwner($developer, $data);

        return true;
    }
}
