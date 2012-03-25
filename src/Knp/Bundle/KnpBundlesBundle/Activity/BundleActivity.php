<?php

namespace Knp\Bundle\KnpBundlesBundle\Activity;

class BundleActivity {
   
    const ACTIVITY_HIGH   = 7;
    const ACTIVITY_MEDIUM = 30;
    const ACTIVITY_LOW    = 90;

    protected static $titles = array(
        self::ACTIVITY_HIGH   => 'bundles.activity.high',
        self::ACTIVITY_MEDIUM => 'bundles.activity.medium',
        self::ACTIVITY_LOW    => 'bundles.activity.low'
    );

    /**
     * Get bundle activity title by days number after last commit
     *
     * @param integer $days
     * @return string
     */
    public static function getActivityByDays($days)
    {
        switch ($days) {
            case ($days <= self::ACTIVITY_HIGH):
                $activity = self::ACTIVITY_HIGH;
                break;

            case ($days <= self::ACTIVITY_MEDIUM):
                $activity = self::ACTIVITY_MEDIUM;
                break;
                
            case ($days <= self::ACTIVITY_LOW):
                $activity = self::ACTIVITY_LOW;
                break;        
            
            default:
                $activity = self::ACTIVITY_LOW;
                break;
        }

        return self::$titles[$activity];
    }
}