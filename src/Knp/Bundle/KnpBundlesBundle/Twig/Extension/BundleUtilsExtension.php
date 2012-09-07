<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BundleUtilsExtension extends \Twig_Extension
{
    const ACTIVITY_HIGH   = 7;
    const ACTIVITY_MEDIUM = 30;
    const ACTIVITY_LOW    = 90;

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'bundle_activity'      => new \Twig_Filter_Method($this, 'bundleActivity'),
            'bundle_state_tooltip' => new \Twig_Filter_Method($this, 'bundleStateTooltip')
        );
    }

    /**
     * Display help message about bundle state
     *
     * @param string $state
     *
     * @return string
     */
    public function bundleStateTooltip($state)
    {
        switch ($state) {
            default:
                return 'status of this bundle is not yet confirmed';
                break;

            case Bundle::STATE_READY:
                return 'this bundle is ready for production usage';
                break;

            case Bundle::STATE_NOT_YET_READY:
                return 'this bundle is currently in development stage, you can use it on your own risk';
                break;

            case Bundle::STATE_DEPRECATED:
                return 'this bundle is not maintained anymore, you can use it on your own risk';
                break;
        }
    }

    /**
     * Get bundle activity title by days number after last commit
     *
     * @param \DateTime $lastCommitAt
     *
     * @return string
     */
    public function bundleActivity(\DateTime $lastCommitAt)
    {
        $days = $lastCommitAt->diff(new \DateTime('now'))->format('%a');
        if ($days <= self::ACTIVITY_HIGH) {
            return 'bundles.activity.high';
        }

        if ($days <= self::ACTIVITY_MEDIUM) {
            return 'bundles.activity.medium';
        }

        return 'bundles.activity.low';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'bundle_utils';
    }
}
