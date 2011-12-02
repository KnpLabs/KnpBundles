<?php

namespace Knp\Bundle\KnpBundlesBundle\Twig\Extension;

use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;

class LocaleRoutingTwigExtension extends \Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'locale_path' => new \Twig_Function_Method($this, 'getLocalizedPath'),
            'locale_url' => new \Twig_Function_Method($this, 'getLocalizedUrl'),
        );
    }

    public function getLocalizedPath(Request $request, $locale)
    {
        return $this->generateLocalizedRoute($request, $locale, false);
    }

    public function getLocalizedUrl(Request $request, $locale)
    {
        return $this->generateLocalizedRoute($request, $locale, true);
    }

    /**
     * Generate localized route
     *
     * @param type $request
     * @param type $locale
     * @param type $absolute
     *
     * @return string
     */
    protected function generateLocalizedRoute(Request $request, $locale, $absolute)
    {
        $route = $request->attributes->get('_route');

        $parameters = array_merge($request->attributes->get('_route_params'), $request->query->all());
        $parameters['_locale'] = $locale;

        return $this->generator->generate($route, $parameters, $absolute);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'locale_routing';
    }

}
