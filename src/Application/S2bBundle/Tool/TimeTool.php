<?php

namespace Application\S2bBundle\Tool;

class TimeTool
{
    /**
     * The function below returns a single number of years, months, days, hours, minutes or seconds between the current date and the provided date.
     * If the date occurs in the past (is negative/inverted), it suffixes it with 'ago'.
     *
     * @return string
     **/
    public static function ago(\DateTime $since)
    {
        $now = new \DateTime();
        $interval = $now->diff($since);
        $suffix = ( $interval->invert ? ' ago' : '' );
        if ( $v = $interval->y >= 1 ) return static::pluralize( $interval->y, 'year' ) . $suffix;
        elseif ( $v = $interval->m >= 1 ) return static::pluralize( $interval->m, 'month' ) . $suffix;
        elseif ( $v = $interval->d >= 1 ) return static::pluralize( $interval->d, 'day' ) . $suffix;
        elseif ( $v = $interval->h >= 1 ) return static::pluralize( $interval->h, 'hour' ) . $suffix;
        elseif ( $v = $interval->i >= 1 ) return static::pluralize( $interval->i, 'minute' ) . $suffix;

        return static::pluralize( $interval->s, 'second' ) . $suffix;
    }

    public static function pluralize($count, $text)
    {
        return $count . ( ( $count == 1 ) ? ( " $text" ) : ( " ${text}s" ) );
    }
}
