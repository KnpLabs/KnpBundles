<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

use Knp\Bundle\TimeBundle\Twig\Extension\TimeExtension;

use Knp\Bundle\KnpBundlesBundle\Entity\Activity;
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
            'bundle_github_url'    => new \Twig_Filter_Method($this, 'bundleGithubUrl'),
            'bundle_packagist_url' => new \Twig_Filter_Method($this, 'bundlePackagistUrl'),
            'bundle_state_tooltip' => new \Twig_Filter_Method($this, 'bundleStateTooltip'),
            'bundle_travis_url'    => new \Twig_Filter_Method($this, 'bundleTravisUrl'),
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'bundle_activity_icon'  => new \Twig_Function_Method($this, 'bundleActivityIcon', array('is_safe' => array('html'))),
            'bundle_activity_msg'   => new \Twig_Function_Method($this, 'bundleActivityMessage'),
        );
    }

    /**
     * @param string $activityType
     *
     * @return string
     */
    public function bundleActivityIcon($activityType)
    {
        switch ($activityType) {
            case Activity::ACTIVITY_TYPE_COMMIT:
                $icon = 'github';
                break;

            case Activity::ACTIVITY_TYPE_RECOMMEND:
                $icon = 'thumbs-up';
                break;

            default:
                $icon = 'info';
        }

        return sprintf('<i class="icon-%s"></i>', $icon);
    }

    /**
     * @param Activity $activity
     * @param string   $type
     *
     * @return string
     */
    public function bundleActivityMessage(Activity $activity, $type = 'long')
    {
        switch ($activity->getType()) {
            case Activity::ACTIVITY_TYPE_COMMIT:
                return 'long' == $type ? $activity->getMessage() : 'commited into';
                break;

            case Activity::ACTIVITY_TYPE_RECOMMEND:
                return 'long' == $type ? 'Bundle was recommended' : 'recommended';
                break;

            case Activity::ACTIVITY_TYPE_TWEETED:
                return 'long' == $type ? 'Bundle archived top trending badge' : 'archived';
                break;

            default:
                return $activity->getMessage() ?: 'Various info';
        }
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
                return 'Status of this bundle is not yet confirmed';
                break;

            case Bundle::STATE_READY:
                return 'This bundle is ready for production usage';
                break;

            case Bundle::STATE_NOT_YET_READY:
                return 'This bundle is currently in development stage, you can use it on your own risk';
                break;

            case Bundle::STATE_DEPRECATED:
                return 'This bundle is not maintained anymore, you can use it on your own risk';
                break;
        }
    }

    /**
     * Get bundle activity title by days number after last commit
     *
     * @param mixed $lastCommitAt
     *
     * @return string
     */
    public function bundleActivity($lastCommitAt)
    {
        if (!$lastCommitAt instanceof \DateTime) {
            $lastCommitAt = new \DateTime('@'.strtotime($lastCommitAt));
        }

        $days = $lastCommitAt->diff(new \DateTime('now'))->format('%a');
        if ($days <= self::ACTIVITY_HIGH) {
            return 'bundles.activity.high';
        }

        if ($days <= self::ACTIVITY_MEDIUM) {
            return 'bundles.activity.medium';
        }

        return 'bundles.activity.low';
    }

    public function bundleGithubUrl(Bundle $bundle, $urlType = 'http')
    {
        if ('git' === $urlType) {
            $url = 'git://github.com/%s/%s.git';
        } else {
            $url = 'http://github.com/%s/%s';
        }

        return sprintf($url, $bundle->getOwnerName(), $this->getName());
    }

    public function bundlePackagistUrl(Bundle $bundle)
    {
        return $bundle->getComposerName() ? sprintf('http://packagist.org/packages/%s', $bundle->getComposerName()) : null;
    }

    public function bundleTravisUrl(Bundle $bundle)
    {
        return $bundle->getUsesTravisCi() ? sprintf('http://travis-ci.org/%s/%s', $bundle->getOwnerName(), $this->getName()) : null;
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
