<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

use Knp\Bundle\KnpBundlesBundle\Activity\BundleActivity;

class BundleActivityTwigExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            'bundle_activity' => new \Twig_Filter_Method($this, 'bundleActivity'),
        );
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
        return 'bundle_activity';
    }
}
