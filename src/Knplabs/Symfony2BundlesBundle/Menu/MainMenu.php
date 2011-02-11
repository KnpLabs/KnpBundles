<?php

namespace Knplabs\Symfony2BundlesBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

use Knplabs\MenuBundle\Menu;

class MainMenu extends Menu
{
    public function __construct(Request $request, Router $router)
    {
        parent::__construct(array(
            'id'    => 'menu'
        ));

        $this->setCurrentUri($request->getRequestUri());

        $this->addChild('Home', $router->generate('homepage', array()), array('class' => 'home'));
        $this->addChild('Bundles', $router->generate('bundle_list', array()));
        $this->addChild('Projects', $router->generate('project_list', array()));
        $this->addChild('Developers', $router->generate('user_list', array()));
    }
}
