<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

/*
 * Everything in src/ is a plain autowired service. The compliance checks are
 * tagged by omnibase/office's autoconfiguration (ComplianceCheckInterface),
 * the audience resolver too. The back office is loaded when omnibase/admin
 * is installed.
 */
return function (ContainerConfigurator $configurator) {
    $src = dirname(__DIR__).'/src';

    $services = $configurator->services();
    $services->defaults()
        ->autowire(true)
        ->autoconfigure(true)
        ->public(false);

    $services->load('Base\\Lawyer\\', $src.'/')
        ->exclude([
            $src.'/DependencyInjection/',
            $src.'/Entity/',
            $src.'/Guard/',
            $src.'/Enum/',
            $src.'/Form/Model/',
            $src.'/Controller/Admin/',
            $src.'/Admin/',
            $src.'/Demo/',
            $src.'/LawyerBundle.php',
        ]);

    // The demonstration accounts of a firm, when the installed glitchr/omnibase has the demo environment.
    if (interface_exists('Base\\Demo\\DemoAccountProviderInterface')) {
        $services->load('Base\\Lawyer\\Demo\\', $src.'/Demo/');
    }

    $services->load('Base\\Lawyer\\Controller\\Client\\', $src.'/Controller/Client/')
        ->tag('controller.service_arguments');

    if (class_exists('Base\\Admin\\Controller\\AbstractCrudController')) {
        $services->load('Base\\Lawyer\\Controller\\Admin\\', $src.'/Controller/Admin/')
            ->tag('controller.service_arguments');
        $services->load('Base\\Lawyer\\Admin\\', $src.'/Admin/');
    }
};
