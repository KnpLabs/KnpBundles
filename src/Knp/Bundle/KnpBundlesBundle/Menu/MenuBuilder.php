<?php

namespace Knp\Bundle\KnpBundlesBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Security\Core\SecurityContext;

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

    public function createMainMenu(Request $request, Translator $translator, SecurityContext $securityContext)
    {
        $menu = $this->factory->createItem('root');
        $menu->setCurrentUri($request->getRequestUri());

        $menu->addChild('bundles', array('route' => 'bundle_list'))->setLabel($translator->trans('menu.bundles'));
        $menu->addChild('users', array('route' => 'user_list'))->setLabel($translator->trans('menu.users'));
        $menu->addChild('evolution', array('route' => 'evolution'))->setLabel($translator->trans('menu.evolution'));
        $menu->addChild('add-bundle', array('route' => 'add_bundle'))->setLabel($translator->trans('menu.addBundleManually'));

        return $menu;
    }
}
