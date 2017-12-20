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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Config\FileLocator;
use JoiPolloi\Bundle\JsonValidationBundle\Annotation\{ValidateJson,ValidateJsonListener};
use JoiPolloi\Bundle\JsonValidationBundle\JsonValidator\JsonValidator;

/**
 * Validate JSON Listener test
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationBundle
 */
class ValidateJsonListenerTest extends TestCase
{
    public function testMissingAttribute()
    {
        $request = new Request();
        $event = $this->getFilterControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $isNull = $listener->onKernelController($event);

        $this->assertNull($isNull);
        $this->assertFalse($request->attributes->has('validJson'));
    }

    public function testInvalidMethod()
    {
        $annotation = new ValidateJson([ 'path' => 'schema-simple.json', 'methods' => [ 'POST' ]]);

        $request = Request::create('/');
        // in the real system this is handled by SensioFrameworkExtraBundle
        $request->attributes->set('_validate_json', $annotation);

        $event = $this->getFilterControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $isNull = $listener->onKernelController($event);

        $this->assertNull($isNull);
        $this->assertFalse($request->attributes->has('validJson'));
    }

    /**
     * @expectedException JoiPolloi\Bundle\JsonValidationBundle\Exception\JsonValidationException
     */
    public function testInvalidJson()
    {
        $annotation = new ValidateJson([ 'path' => 'schema-simple.json' ]);

        $request = Request::create('/', 'POST', [], [], [], [], '{invalid');
        $request->attributes->set('_validate_json', $annotation);

        $event = $this->getFilterControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);
    }

    public function testValidJson()
    {
        $annotation = new ValidateJson([ 'path' => 'schema-simple.json' ]);

        $request = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set('_validate_json', $annotation);

        $event = $this->getFilterControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $isNull = $listener->onKernelController($event);

        $this->assertNull($isNull);
        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertEquals($request->attributes->get('validJson')->test, 'hello');
    }

    public function testValidJsonArray()
    {
        $annotation = new ValidateJson([ 'path' => 'schema-simple.json' ]);
        $request = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set('_validate_json', $annotation);

        $kernel = $this->getMockBuilder(HttpKernelInterface::class)
            ->getMock();
        $controller = function(array $validJson) { };
        $type = HttpKernelInterface::MASTER_REQUEST;
        $event = new FilterControllerEvent($kernel, $controller, $request, $type);

        $listener = $this->getValidateJsonListener();

        $isNull = $listener->onKernelController($event);

        $this->assertNull($isNull);
        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertTrue(is_array($request->attributes->get('validJson')));
        $this->assertEquals($request->attributes->get('validJson')['test'], 'hello');
    }

    protected function getValidateJsonListener() : ValidateJsonListener
    {
        $locator = new FileLocator([ __DIR__ ]);
        $validator = new JsonValidator($locator);
        return new ValidateJsonListener($validator);
    }

    protected function getFilterControllerEvent(Request $request) : FilterControllerEvent
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)
            ->getMock();
        $controller = function($validJson) { };
        $type = HttpKernelInterface::MASTER_REQUEST;

        return new FilterControllerEvent($kernel, $controller, $request, $type);
    }
}
