<?php

namespace Knp\Bundle\KnpBundlesBundle\Github;

use Github\Api\User;
use Github\Exception\ApiLimitExceedException;
use Github\Exception\RuntimeException;

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

        /** @var User $api */
        $api = $this->github->api('user');

        try {
            $data = $api->show($developer->getName());
        } catch(ApiLimitExceedException $e) {
            return false;
        } catch(RuntimeException $e) {
            // Not found via actual name? Search by other known data
            foreach ($keywords as $field) {
                // Did we found user in this iteration ?
                if (!empty($data)) {
                    break;
                }

                try {
                    $data = $api->search($field);
                    if (isset($data['users']) && 0 < count($data['users'])) {
                        $data = $data['users'][0];
                        // Let's call API one more time to get clean user data
                        $data = $api->show($data['login']);
                    }
                } catch(ApiLimitExceedException $e) {
                    // Api limit ? Then not do anything more
                    return false;
                } catch(RuntimeException $e) {
                    // Not found yet ? Continue loop
                }
            }
        }

        // Developer has been removed / not found ?
        if (empty($data)) {
            return false;
        }

        $this->updateOwner($developer, $data);

        return true;
    }
}
