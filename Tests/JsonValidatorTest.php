<?php

/*
 * This file is part of the JsonValidationBundle package.
 *
 * (c) John Noel <john.noel@joipolloi.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use JoiPolloi\Bundle\JsonValidationBundle\JsonValidator\JsonValidator;

/**
 * JSON Validator tests
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class JsonValidatorTest extends TestCase
{
    public function testMissingSchema()
    {
        $validator = $this->getJsonValidator();

        $isNull = $validator->validateJson('{}', 'nonexistent-schema.json');
        $errors = $validator->getValidationErrors();

        $this->assertNull($isNull);
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0]['message'], 'Unable to locate schema nonexistent-schema.json');
    }

    public function testSyntacticallyInvalidJson()
    {
        $validator = $this->getJsonValidator();

        $isNull = $validator->validateJson('{invalid', 'schema-simple.json');
        $errors = $validator->getValidationErrors();

        $this->assertNull($isNull);
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0]['message'], '[4] Syntax error');
    }

    public function testSchemaInvalidJson()
    {
        $validator = $this->getJsonValidator();

        $isNull = $validator->validateJson('{"nottest": "hello"}', 'schema-simple.json');
        $errors = $validator->getValidationErrors();

        $this->assertNull($isNull);
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0]['message'], 'The property test is required');
    }

    public function testInvalidSchema()
    {
        $validator = $this->getJsonValidator();

        $isNull = $validator->validateJson('{"test": "hello"}', 'schema-invalid.json');
        $errors = $validator->getValidationErrors();

        $this->assertNull($isNull);
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0]['message'], 'JSON syntax is malformed');
    }

    public function testValidJson()
    {
        $validator = $this->getJsonValidator();

        $object = $validator->validateJson('{"test": "hello"}', 'schema-simple.json');
        $errors = $validator->getValidationErrors();

        $this->assertNotNull($object);
        $this->assertCount(0, $errors);
        $this->assertEquals('hello', $object->test);
    }

    public function testValidEmptyJsonRequest()
    {
        $validator = $this->getJsonValidator();
        $request = new Request([], [], [], [], [], [], '');

        $isTrue = $validator->validateJsonRequest($request, 'schema-simple.json', true);
        $errors = $validator->getValidationErrors();

        $this->assertNotNull($isTrue);
        $this->assertCount(0, $errors);
        $this->assertTrue($isTrue);
    }

    public function testInvalidEmptyResponse()
    {
        $validator = $this->getJsonValidator();
        $request = new Request([], [], [], [], [], [], '');

        $isNull = $validator->validateJsonRequest($request, 'schema-simple.json', false);
        $errors = $validator->getValidationErrors();

        $this->assertNull($isNull);
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0]['message'], '[4] Syntax error');
    }

    public function testValidJsonRequest()
    {
        $validator = $this->getJsonValidator();
        $request = new Request([], [], [], [], [], [], '{"test": "hello"}');

        $object = $validator->validateJsonRequest($request, 'schema-simple.json', false);
        $errors = $validator->getValidationErrors();

        $this->assertNotNull($object);
        $this->assertCount(0, $errors);
        $this->assertEquals($object->test, 'hello');
    }

    protected function getJsonValidator() : JsonValidator
    {
        $locator = new FileLocator([ __DIR__ ]);
        return new JsonValidator($locator);
    }
}
