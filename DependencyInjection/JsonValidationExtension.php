<?php

namespace Mrsuh\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Extension\Extension;

class JsonValidationExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator([
            __DIR__ . '/../Resources/config/'
        ]));

        $loader->load('services.xml');

        if ($config['enable_request_listener']) {
            $container->getDefinition('mrsuh_json_validation.request_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.controller', 'priority' => -100]);
        }

        if ($config['enable_response_listener']) {
            $container->getDefinition('mrsuh_json_validation.response_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.response', 'priority' => -100]);
        }

        if ($config['enable_exception_listener']) {
            $container->getDefinition('mrsuh_json_validation.exception_listener')
                      ->addTag('kernel.event_listener', ['event' => 'kernel.exception']);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias(): string
    {
        return 'mrsuh_json_validation';
    }
}
