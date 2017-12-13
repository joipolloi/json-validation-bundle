<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoiPolloi\Bundle\JsonValidationBundle\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * JSON validation exception
 *
 * Triggered when JSON validation fails and can be caught separately in order
 * to throw, for instance, application/problem+json response
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class JsonValidationException extends BadRequestHttpException
{
}
