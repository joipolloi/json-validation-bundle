<?php

namespace JoiPolloi\Bundle\JsonValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * JSON Validation bundle
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class JsonValidationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
