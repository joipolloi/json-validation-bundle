<?php

namespace Mrsuh\JsonValidationBundle\EventListener;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ValidateJsonResponseListener
{
    use AnnotationReader;

    protected JsonValidator   $jsonValidator;
    protected LoggerInterface $logger;

    public function __construct(JsonValidator $jsonValidator, LoggerInterface $logger)
    {
        $this->jsonValidator = $jsonValidator;
        $this->logger        = $logger;
    }

    /**
     * @param ResponseEvent|FilterResponseEvent $event
     */
    public function onKernelResponse($event): void
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        $annotation = self::getAnnotation($request, ValidateJsonResponse::class);
        if ($annotation === null) {
            return;
        }

        if (!empty($annotation->getStatuses()) && !in_array($response->getStatusCode(), $annotation->getStatuses())) {
            return;
        }

        $content = $response->getContent();

        if ($annotation->getEmptyIsValid() && empty($content)) {
            return;
        }

        $this->jsonValidator->validate(
            $content,
            $annotation->getPath()
        );

        if (!empty($this->jsonValidator->getErrors())) {
            $this->logger->warning('Json response validation',
                [
                    'uri'        => $request->getUri(),
                    'schemaPath' => $annotation->getPath(),
                    'errors'     => $this->jsonValidator->getErrors()
                ]
            );
        }
    }
}
