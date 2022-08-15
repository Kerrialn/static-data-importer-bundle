<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StaticDataImporterBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}