<?php

namespace Kerrialn\Bundle\StaticDataImporterBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class StaticDataImporterBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}