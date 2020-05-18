<?php

namespace Mrsuh\JsonValidationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder('mrsuh_jsonvalidation');
        $tb->getRootNode()
           ->children()
           ->booleanNode('enable_request_listener')->defaultTrue()->end()
           ->booleanNode('enable_response_listener')->defaultTrue()->end()
           ->booleanNode('enable_exception_listener')->defaultTrue()->end();

        return $tb;
    }
}
