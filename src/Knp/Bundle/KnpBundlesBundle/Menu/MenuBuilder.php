<?php

namespace Knp\Bundle\KnpBundlesBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMainMenu(Request $request, Translator $translator)
    {
        $menu = $this->factory->createItem('root');
        $menu->setCurrentUri($request->getRequestUri());

        $menu->addChild($translator->trans('menu.bundles'), array('route' => 'bundle_list'));
        $menu->addChild($translator->trans('menu.projects'), array('route' => 'project_list'));
        $menu->addChild($translator->trans('menu.users'), array('route' => 'user_list'));
        $menu->addChild($translator->trans('menu.name'), array('uri' => $translator->trans('menu.urls.name')));

        return $menu;
    }
}
