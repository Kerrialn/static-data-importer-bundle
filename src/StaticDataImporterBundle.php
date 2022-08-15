<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle;

use Kerrialn\Bundle\StaticDataImporterBundle\DependencyInjection\StaticDataImporterExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StaticDataImporterBundle extends AbstractBundle
{
    public function getContainerExtension() : ExtensionInterface
    {
        return new StaticDataImporterExtension();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $configDir =__DIR__.'/../../../src/config/services.xml';

        if(is_dir($configDir) === false){
            return;
        }

        $container->import($configDir);
    }

}