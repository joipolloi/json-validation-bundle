<?php

namespace Mrsuh\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class JsonValidationExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator([
            __DIR__ . '/../Resources/config/'
        ]));

        $loader->load('services.xml');

        if ($config['enable_request_listener']) {
            $container->getDefinition('mrsuh_jsonvalidation.validate_json_request_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.controller', 'priority' => -100]);
        }

        if ($config['enable_response_listener']) {
            $container->getDefinition('mrsuh_jsonvalidation.validate_json_response_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.response', 'priority' => -100]);
        }

        if ($config['enable_exception_listener']) {
            $container->getDefinition('mrsuh_jsonvalidation.json_validation_exception_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.exception', 'priority' => -100]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return 'mrsuh_jsonvalidation';
    }
}
