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
use JoiPolloi\Bundle\JsonValidationBundle\Annotation\ValidateJson;

/**
 * Validate JSON annotation test
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class ValidateJsonTest extends TestCase
{
    /**
     * @dataProvider constructorOptionsProvider
     */
    public function testConstructorOptions(array $options, string $expectedPath, bool $expectedEmptyIsValid, array $expectedMethods)
    {
        $annotation = new ValidateJson($options);

        $this->assertEquals($annotation->getPath(), $expectedPath);
        $this->assertEquals($annotation->getEmptyIsValid(), $expectedEmptyIsValid);
        $this->assertEquals($annotation->getMethods(), $expectedMethods);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidConstructorOptions()
    {
        new ValidateJson([ 'invalid_option' => 'yes' ]);
    }

    public function constructorOptionsProvider() : array
    {
        return [
            [ [ 'value' => 'abc' ], 'abc', false, [] ],
            [ [ 'path' => 'abc' ], 'abc', false, [] ],
            [ [ 'path' => 'abc', 'emptyIsValid' => true ], 'abc', true, [] ],
            [ [ 'path' => 'abc', 'methods' => [ 'POST', 'PUT' ] ], 'abc', false, [ 'POST', 'PUT' ] ],
        ];
    }
}
