<?php

namespace Mrsuh\JsonValidationBundle\EventListener;

use Mrsuh\JsonValidationBundle\Exception\JsonValidationRequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ValidateJsonExceptionListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ExceptionEvent|GetResponseForExceptionEvent $event
     */
    public function onKernelException($event): void
    {
        if (method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } else {
            $exception = $event->getException();
        }

        if (!$exception instanceof JsonValidationRequestException) {
            return;
        }

        $data = [
            'status' => Response::HTTP_BAD_REQUEST,
            'title'  => 'Unable to parse/validate JSON',
            'detail' => 'There was a problem with the JSON that was sent with the request',
            'errors' => $this->formatErrors($exception->getErrors()),
        ];

        $event->setResponse(
            new JsonResponse(
                $data,
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/problem+json']
            )
        );

        $this->logger->error('Json request validation',
            [
                'uri'        => $exception->getRequest()->getUri(),
                'schemaPath' => $exception->getSchemaPath(),
                'errors'     => $exception->getErrors()
            ]
        );
    }

    protected function formatErrors(array $errors): array
    {
        return array_map('array_filter', $errors);
    }
}
