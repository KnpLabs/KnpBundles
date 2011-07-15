<?php

namespace Knp\Bundle\Symfony2BundlesBundle\Menu;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Knp\Bundle\MenuBundle\Menu;

class MainMenu extends Menu
{
    public function __construct(Request $request, UrlGeneratorInterface $router)
    {
        parent::__construct(array(
            'id'    => 'menu'
        ));

        $this->setCurrentUri($request->getRequestUri());

        $this->addChild('Bundles', $router->generate('bundle_list', array()));
        $this->addChild('Projects', $router->generate('project_list', array()));
        $this->addChild('Developers', $router->generate('user_list', array()));
        $this->addChild('Commercial services', 'http://www.knplabs.com/');
    }
}
