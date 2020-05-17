<?php

namespace Mrsuh\JsonValidationBundle\JsonValidator;

use JsonSchema\Exception\JsonDecodingException;
use JsonSchema\Validator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocatorInterface;

class JsonValidator
{
    /** @var FileLocatorInterface */
    protected $locator;

    protected $logger;
    /** @var array */
    protected $errors = [];

    /**
     * @param FileLocatorInterface $locator
     */
    public function __construct(LoggerInterface $logger, FileLocatorInterface $locator)
    {
        $this->locator = $locator;
        $this->logger  = $logger;
    }

    /**
     * Validate JSON against a schema
     *
     * @param string $json
     * @param string $schemaPath
     * @param bool   $asArray Whether to decode the JSON as an associative array
     * @return mixed The decoded JSON as an object (stdClass) if the JSON is
     *                        valid, otherwise null
     */
    public function validate(string $json, string $schemaPath): void
    {
        $this->errors = [];
        $schema       = null;

        try {
            $schema = $this->locator->locate($schemaPath);
        } catch (\InvalidArgumentException $e) {
            $this->errors[] = [
                'property'   => null,
                'pointer'    => null,
                'message'    => 'Unable to locate schema ' . $schemaPath,
                'constraint' => null,
            ];

            return;
        }

        $data = json_decode($json);

        if ($data === null) {
            $this->errors[] = [
                'property'   => null,
                'pointer'    => null,
                'message'    => '[' . json_last_error() . '] ' . json_last_error_msg(),
                'constraint' => null,
            ];

            return;
        }

        $validator = new Validator();

        try {
            $validator->check($data, (object)['$ref' => 'file://' . $schema]);
        } catch (JsonDecodingException $e) {
            $this->errors[] = [
                'property'   => null,
                'pointer'    => null,
                'message'    => $e->getMessage(),
                'constraint' => null,
            ];

            return;
        }

        if (!$validator->isValid()) {
            $this->errors = $validator->getErrors();

            return;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
