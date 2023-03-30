<?php

namespace Tests;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use PHPUnit\Framework\TestCase;

class ValidateJsonResponseTest extends TestCase
{
    /**
     * @dataProvider constructorOptionsProvider
     */
    public function testConstructorOptions(array $options, string $expectedPath, bool $expectedEmptyIsValid, array $expectedStatuses)
    {
        $annotation = new ValidateJsonResponse($options);

        $this->assertEquals($annotation->getPath(), $expectedPath);
        $this->assertEquals($annotation->getEmptyIsValid(), $expectedEmptyIsValid);
        $this->assertEquals($annotation->getStatuses(), $expectedStatuses);
    }

    public static function constructorOptionsProvider(): array
    {
        return [
            [['value' => 'abc'], 'abc', false, []],
            [['path' => 'abc'], 'abc', false, []],
            [['path' => 'abc', 'emptyIsValid' => true], 'abc', true, []],
            [['path' => 'abc', 'statuses' => [200, 201]], 'abc', false, [200, 201]],
        ];
    }
}
