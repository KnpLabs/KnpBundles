<?php

/**
 * frontUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class frontUrlMatcher extends Symfony\Components\Routing\Matcher\UrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(array $context = array(), array $defaults = array())
    {
        $this->context = $context;
        $this->defaults = $defaults;
    }

    public function match($url)
    {
        $url = $this->normalizeUrl($url);

        if (preg_match('#^/$#x', $url, $matches)) {
            return array_merge($this->mergeDefaults($matches, array (  '_controller' => 'FrontBundle:Default:index',)), array('_route' => 'homepage'));
        }

        return false;
    }
}
