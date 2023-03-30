<?php

namespace Mrsuh\JsonValidationBundle\Exception;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class JsonValidationRequestException extends BadRequestHttpException
{
    protected array   $errors     = [];
    protected Request $request;
    protected string  $schemaPath = '';

    public function __construct(Request $request, string $schemaPath, array $errors = [])
    {
        $this->request    = $request;
        $this->schemaPath = $schemaPath;
        $this->errors     = $errors;

        parent::__construct('Json request validation error');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSchemaPath(): string
    {
        return $this->schemaPath;
    }
}
