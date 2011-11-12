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
        $route = $request->attributes->get('_route');
        
        $parameters = $request->attributes->all();
        unset($parameters['_route']);
        unset($parameters['_controller']);
        unset($parameters['_route_params']);

        $parameters = array_merge($parameters, $request->query->all());
        $parameters['_locale'] = $locale;

        return $this->generator->generate($route, $parameters, false);
    }

    public function getLocalizedUrl(Request $request, $locale)
    {
        $route = $request->attributes->get('_route');
        
        $parameters = $request->attributes->all();
        unset($parameters['_route']);
        unset($parameters['_controller']);
        unset($parameters['_route_params']);

        $parameters = array_merge($parameters, $request->query->all());
        $parameters['_locale'] = $locale;

        return $this->generator->generate($route, $parameters, true);
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
