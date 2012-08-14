<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

use Knp\Bundle\KnpBundlesBundle\Activity\BundleActivity;
use Knp\Bundle\KnpBundlesBundle\Entity\Bundle;

class BundleUtilsExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'bundle_activity' => new \Twig_Filter_Method($this, 'bundleActivity'),
            'bundle_state_tooltip' => new \Twig_Filter_Method($this, 'bundleStateTooltip')
        );
    }

    /**
     * Display help message about bundle state
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
     * Display bundle activity
     *
     * @param DateTime $lastCommitAt
     * @return string
     */
    public function bundleActivity(\DateTime $lastCommitAt)
    {
        return BundleActivity::getActivityByDays(
            $lastCommitAt->diff(new \DateTime('now'))->format('%a')
        );
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
