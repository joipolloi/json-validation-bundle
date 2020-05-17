<?php

namespace Mrsuh\JsonValidationBundle\EventListener;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ValidateJsonResponseListener
{
    /** @var JsonValidator */
    protected $jsonValidator;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(JsonValidator $jsonValidator, LoggerInterface $logger)
    {
        $this->jsonValidator = $jsonValidator;
        $this->logger        = $logger;
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();

        $annotationAlias = sprintf('_%s', ValidateJsonResponse::ALIAS);

        if (!$request->attributes->has($annotationAlias)) {
            return;
        }

        /** @var ValidateJsonResponse $annotation */
        $annotation = $request->attributes->get($annotationAlias);

        if (!empty($response->getStatusCode()) && !in_array($response->getStatusCode(), $annotation->getStatuses())) {
            return;
        }

        $content = $request->getContent();

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
