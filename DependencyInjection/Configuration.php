<?php

namespace Mrsuh\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('mrsuh_json_validation');
        $treeBuilder
            ->getRootNode()
            ->children()
            ->booleanNode('enable_request_listener')->defaultTrue()->end()
            ->booleanNode('enable_response_listener')->defaultTrue()->end()
            ->booleanNode('enable_exception_listener')->defaultTrue()->end();

        return $treeBuilder;
    }
}
