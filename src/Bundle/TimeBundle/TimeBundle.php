<?php

namespace Bundle\TimeBundle;

use Bundle\TimeBundle\DependencyInjection\TimeExtension;
use Symfony\Framework\Bundle\Bundle as BaseBundle;
use Symfony\Components\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Components\DependencyInjection\ContainerBuilder;

class TimeBundle extends BaseBundle
{
    public function buildContainer(ParameterBagInterface $parameterBag)
    {
        ContainerBuilder::registerExtension(new TimeExtension());
    }
}
