<?php

namespace Mrsuh\JsonValidationBundle\Exception;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonValidationRequestException extends BadRequestHttpException
{
    /** @var array */
    protected $errors = [];

    /** @var Request */
    protected $request;

    /** @var ValidateJsonRequest */
    protected $annotation;

    /**
     * @param string $message The exception message
     * @param array  $errors  Any validation errors
     */
    public function __construct(string $message, Request $request, ValidateJsonRequest $annotation, array $errors = [])
    {
        $this->request    = $request;
        $this->annotation = $annotation;
        $this->errors     = $errors;

        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getAnnotation(): ValidateJsonRequest
    {
        return $this->annotation;
    }
}
