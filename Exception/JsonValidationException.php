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
    /** @var array */
    protected $errors;

    /**
     * @param string $message The exception message
     * @param array $errors Any validation errors
     */
    public function __construct(string $message, array $errors = [])
    {
        $this->errors = $errors;

        parent::__construct($message);
    }

    /**
     * Get the validation errors (if any) that triggered this exception
     *
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }
}
