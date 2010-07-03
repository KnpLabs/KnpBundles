<?php

namespace Application\S2bBundle\Controller;

use Symfony\Framework\FoundationBundle\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('S2bBundle:Default:index');
    }
}
