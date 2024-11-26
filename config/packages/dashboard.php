<?php

// config/packages/twig.php
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig) {
    // ...

    // $twig->global('uuid')->value(service('App\Generator\UuidGenerator'));
};