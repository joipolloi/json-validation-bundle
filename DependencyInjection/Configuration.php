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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * Bundle configuration
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationExtension
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $tb->root('joipolloi_jsonvalidation', 'array')
            ->children()
                ->booleanNode('enable_problemjson_listener')->defaultFalse()->end()
        ;

        return $tb;
    }
}
