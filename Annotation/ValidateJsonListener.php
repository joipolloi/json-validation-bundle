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

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JoiPolloi\Bundle\JsonValidationBundle\JsonValidator\JsonValidator,
    JoiPolloi\Bundle\JsonValidationBundle\Exception\JsonValidationException;

/**
 * Validate JSON listener
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class ValidateJsonListener
{
    /** @var JsonValidator */
    protected $jsonValidator;

    /**
     * @param JsonValidator $jsonValidator
     */
    public function __construct(JsonValidator $jsonValidator)
    {
        $this->jsonValidator = $jsonValidator;
    }

    /**
     * @param FilterControllerEvent $event
     */
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
            $annotation->getEmptyIsValid(),
            $this->getAsArray($event->getController())
        );

        if ($validJson === null) {
            $errors = $this->jsonValidator->getValidationErrors();
            throw new JsonValidationException('Invalid JSON passed', $errors);
        }

        // allow for controller methods to receive the validated JSON
        $request->attributes->set('validJson', $validJson);
    }

    /**
     * Decide whether the validated JSON should be decoded as an array
     *
     * This is based upon the type hint for the $validJson argument
     *
     * @return bool
     * @see Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener::onKernelController
     */
    protected function getAsArray($controller) : bool
    {
        $r = null;

        if (is_array($controller)) {
            $r = new \ReflectionMethod($controller[0], $controller[1]);
        } elseif (is_object($controller) && is_callable($controller, '__invoke')) {
            $r = new \ReflectionMethod($controller, '__invoke');
        } else {
            $r = new \ReflectionFunction($controller);
        }

        foreach ($r->getParameters() as $param) {
            if ($param->getName() !== 'validJson') {
                continue;
            }

            return $param->isArray();
        }

        return false;
    }
}
