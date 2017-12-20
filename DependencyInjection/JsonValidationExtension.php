<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoiPolloi\Bundle\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * Bundle extension
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationExtension
 */
class JsonValidationExtension extends ConfigurableExtension
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

        if ($config['enable_problemjson_listener']) {
            $container->getDefinition('joipolloi_jsonvalidation.listener.jsonvalidationexception')
                ->addTag('kernel.event_listener', [ 'event' => 'kernel.exception' ]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return 'joipolloi_jsonvalidation';
    }
}
