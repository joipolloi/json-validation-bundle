<?php

namespace Mrsuh\JsonValidationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Mrsuh\JsonValidationBundle\DependencyInjection\JsonValidationExtension;

class JsonValidationBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function getContainerExtension()
    {
        return new JsonValidationExtension();
    }
}
