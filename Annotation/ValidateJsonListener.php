<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoiPolloi\Bundle\JsonValidationBundle\Annotation;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JoiPolloi\Bundle\JsonValidationBundle\JsonValidator\JsonValidator;

/**
 * Validate JSON listener
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class ValidateJsonListener
{
    /** @var AnnotationReader */
    protected $reader;
    /** @var JsonValidator */
    protected $jsonValidator;

    /**
     * @param AnnotationReader $reader
     */
    public function __construct(Reader $reader, JsonValidator $jsonValidator)
    {
        $this->reader = $reader;
        $this->jsonValidator = $jsonValidator;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_validate_json')) {
            return;
        }

        $annotation = $request->attributes->get('_validate_json');

        $httpMethods = array_map(function($method) {
            return strtoupper($method);
        }, $annotation->getMethods());

        // if the annotation binds to a particular HTTP method and this request
        // isn't that method, just return
        if (!empty($httpMethods) && !in_array($request->getMethod(), $httpMethods)) {
            return;
        }

        $validJson = $this->jsonValidator->validateJsonRequest(
            $request,
            $annotation->getPath(),
            $annotation->getEmptyIsValid()
        );

        if ($validJson === null) {
            $errors = $this->jsonValidator->getValidationErrors();
            throw new BadRequestHttpException('Invalid JSON passed: '.$this->formatError($errors[0]));
        }

        // allow for controller methods to receive the validated JSON
        $request->attributes->set('validJson', $validJson);
    }

    /**
     * Format a validation error for display
     *
     * @param array $error
     * @return string
     */
    protected function formatError(array $error)
    {
        if (!empty($error['constraint'])) {
            return sprintf('[%s] %s', $error['constraint'], $error['message']);
        }

        return $error['message'];
    }
}
