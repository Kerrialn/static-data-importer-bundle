<?php

namespace Sdi;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class SdiBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // load an XML, PHP or Yaml file
        $container->import('/../../../config/services.yaml');

        // you can also add or replace parameters and services
//        $container->parameters()
//            ->set('acme_hello.phrase', $config['phrase'])
//        ;
    }
}