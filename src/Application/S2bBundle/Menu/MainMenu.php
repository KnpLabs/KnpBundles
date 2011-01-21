<?php

namespace Application\S2bBundle\Menu;
use Bundle\MenuBundle\Menu;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Routing\Router;

class MainMenu extends Menu
{
    public function __construct(Request $request, Router $router)
    {
        parent::__construct();

        $this->setCurrentUri($request->getRequestUri());

        $this->addChild('Home', $router->generate('homepage', array()));
        $this->addChild('Bundles', $router->generate('bundle_list', array()));
        $this->addChild('Projects', $router->generate('project_list', array()));
        $this->addChild('Developers', $router->generate('user_list', array()));
        $this->addChild('Search', $router->generate('search', array()));
        $this->addChild('Api', $router->generate('api', array()));
        $this->addChild('Feed', $router->generate('latest', array('_format' => 'atom')));
    }
}
