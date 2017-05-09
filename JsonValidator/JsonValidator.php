<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JoiPolloi\Bundle\JsonValidationBundle\JsonValidator;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\HttpFoundation\Request;
use JsonSchema\Validator;

/**
 * JSON validator
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class JsonValidator
{
    /** @var FileLocatorInterface */
    protected $locator;
    /** @var array */
    protected $validationErrors = [];

    /**
     * @param FileLocatorInterface $locator
     */
    public function __construct(FileLocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Validate JSON against a schema
     *
     * @param string $json
     * @param string $schemaPath
     * @return mixed The decoded JSON as an object (stdClass) if the JSON is
     *               valid, otherwise null
     */
    public function validateJson($json, $schemaPath)
    {
        $this->validationErrors = [];
        $schema = null;

        try {
            $schema = $this->locator->locate($schemaPath);
        } catch (\InvalidArgumentException $e) {
            $this->validationErrors[] = [
                'property' => null,
                'pointer' => null,
                'message' => 'Unable to locate schema '.$schemaPath,
                'constraint' => null,
            ];

            return null;
        }

        $data = json_decode($json);

        if ($data === null) {
            $this->validationErrors[] = [
                'property' => null,
                'pointer' => null,
                'message' => '['.json_last_error().'] '.json_last_error_msg(),
                'constraint' => null,
            ];

            return null;
        }

        $validator = new Validator();
        $validator->check($data, (object)[ '$ref' => 'file://'.$schema ]);

        if (!$validator->isValid()) {
            $this->validationErrors = $validator->getErrors();
            return null;
        }

        return $data;
    }

    /**
     * Validate the body of a request as JSON
     *
     * @param Request $request
     * @param string $schemaPath
     * @param bool $emptyIsValid Whether an empty request is considered valid
     * @return bool
     */
    public function validateJsonRequest(Request $request, $schemaPath, $emptyIsValid = false)
    {
        $content = $request->getContent();

        if ($emptyIsValid && empty($content)) {
            return true;
        }

        return $this->validateJson($content, $schemaPath);
    }

    /**
     * Get the validation errors that the last validate call produced
     *
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}
