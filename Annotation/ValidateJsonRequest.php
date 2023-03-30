<?php

namespace Mrsuh\JsonValidationBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class ValidateJsonRequest
{
    private string $path         = '';
    private bool   $emptyIsValid = false;
    private array  $methods      = [];

    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $this->path = $data['value'];
        }
        if (isset($data['path'])) {
            $this->path = $data['path'];
        }
        if (isset($data['emptyIsValid'])) {
            $this->emptyIsValid = $data['emptyIsValid'];
        }
        if (isset($data['methods'])) {
            $this->methods = $data['methods'];
        }
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setEmptyIsValid(bool $emptyIsValid): void
    {
        $this->emptyIsValid = $emptyIsValid;
    }

    public function getEmptyIsValid(): bool
    {
        return $this->emptyIsValid;
    }

    public function setMethods($methods): void
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }

        $this->methods = $methods;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }
}
