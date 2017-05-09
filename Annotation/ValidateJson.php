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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Validate JSON annotation @ValidateJson()
 *
 * @Annotation
 * @Target({"METHOD"})
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class ValidateJson extends ConfigurationAnnotation
{
    /**
     * The path to the JSON schema
     *
     * @var string
     */
    private $path;
    /**
     * Whether an empty JSON request value is valid
     *
     * @var bool
     */
    private $emptyIsValid = false;
    /**
     * Only validate on certain HTTP method(s)
     *
     * @var array
     */
    private $methods = [];

    /**
     * @param array $data An array of key/value parameters
     * @throws BadMethodCallException
     * @see Symfony\Component\Routing\Annotation\Route
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['path'] = $data['value'];
            unset($data['value']);
        }

        parent::__construct($data);
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setEmptyIsValid(bool $emptyIsValid)
    {
        $this->emptyIsValid = (bool)$emptyIsValid;
    }

    public function getEmptyIsValid() : bool
    {
        return $this->emptyIsValid;
    }

    public function setMethods($methods)
    {
        if (is_string($methods)) {
            $methods = [ $methods ];
        }

        $this->methods = $methods;
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    /**
     * {@inheritDoc}
     */
    public function getAliasName()
    {
        return 'validate_json';
    }

    /**
     * {@inheritDoc}
     */
    public function allowArray()
    {
        return false;
    }
}
