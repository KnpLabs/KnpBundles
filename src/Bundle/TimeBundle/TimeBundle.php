<?php

namespace Bundle\TimeBundle;

use Bundle\TimeBundle\DependencyInjection\TimeExtension;
use Symfony\Framework\Bundle\Bundle as BaseBundle;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\Loader\Loader;

class TimeBundle extends BaseBundle
{
    public function buildContainer(ParameterBagInterface $parameterBag)
    {
        Loader::registerExtension(new TimeExtension());
    }
}
