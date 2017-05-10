<?php

namespace JoiPolloi\Bundle\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * Bundle extension
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationExtension
 */
class JoiPolloiJsonValidationExtension extends ConfigurableExtension
{
    /**
     * {@inheritDoc}
     */
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator([
            __DIR__.'/../Resources/config/'
        ]));

        $loader->load('services.xml');
    }
}
