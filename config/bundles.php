<?php

use Kerrialn\Bundle\StaticDataImporterBundle\StaticDataImporterBundle;

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    StaticDataImporterBundle::class => ['all' => true]
];
