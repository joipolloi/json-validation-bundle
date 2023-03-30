<?php

namespace Mrsuh\JsonValidationBundle\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class ValidateJsonResponse
{
    private string $path         = '';
    private bool   $emptyIsValid = false;
    public array   $statuses     = [];

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
        if (isset($data['statuses'])) {
            $this->statuses = $data['statuses'];
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

    public function setStatuses($statuses): void
    {
        if (is_string($statuses)) {
            $this->statuses = [$statuses];

            return;
        }

        $this->statuses = $statuses;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }
}
