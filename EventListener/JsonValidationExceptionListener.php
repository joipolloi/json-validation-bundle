<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoiPolloi\Bundle\JsonValidationBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use JoiPolloi\Bundle\JsonValidationBundle\Exception\JsonValidationException;

/**
 * JSON validation exception listener
 *
 * Listens for the JsonValidationException and will return an
 * application/problem+json response
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationExtension
 */
class JsonValidationExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!($exception instanceof JsonValidationException)) {
            return;
        }

        $data = [
            'status' => 400,
            'title' => 'Unable to parse/validate JSON',
            'detail' => 'There was a problem with the JSON that was sent with the request',
            'errors' => $this->formatErrors($exception->getErrors()),
        ];

        $event->setResponse(
            new Response(
                json_encode($data),
                400,
                [ 'Content-Type' => 'application/problem+json' ]
            )
        );
    }

    /**
     * Format the validation errors into something more descriptive
     *
     * @return array
     */
    protected function formatErrors(array $errors) : array
    {
        return array_map('array_filter', $errors);
    }
}
