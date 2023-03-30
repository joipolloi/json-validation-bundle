<?php

namespace Tests;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Mrsuh\JsonValidationBundle\EventListener\AnnotationReader;
use Mrsuh\JsonValidationBundle\EventListener\ValidateJsonRequestListener;
use Mrsuh\JsonValidationBundle\Exception\JsonValidationRequestException;
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ValidateJsonRequestListenerTest extends TestCase
{
    use AnnotationReader;

    public function testMissingAttribute()
    {
        $request  = new Request();
        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertFalse($request->attributes->has('validJson'));
    }

    public function testInvalidMethod()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json', 'methods' => ['POST']]);

        $request = Request::create('/');
        // in the real system this is handled by SensioFrameworkExtraBundle
        $request->attributes->set(ValidateJsonRequest::class, $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertFalse($request->attributes->has('validJson'));
    }

    public function testInvalidJson()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);

        $request = Request::create('/', 'POST', [], [], [], [], '{invalid');
        $request->attributes->set(ValidateJsonRequest::class, $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $this->expectException(JsonValidationRequestException::class);
        $listener->onKernelController($event);
    }

    public function testValidJson()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);

        $request = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set(ValidateJsonRequest::class, $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertEquals('hello', $request->attributes->get('validJson')->test);
    }

    public function testValidJsonArray()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);
        $request    = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set(ValidateJsonRequest::class, $annotation);

        $kernel     = $this->getMockBuilder(HttpKernelInterface::class)
                           ->getMock();
        $controller = function (array $validJson) {
        };
        $type       = HttpKernelInterface::MAIN_REQUEST;
        $event      = new ControllerEvent($kernel, $controller, $request, $type);

        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertTrue(is_array($request->attributes->get('validJson')));
        $this->assertEquals('hello', $request->attributes->get('validJson')['test']);
    }

    protected function getValidateJsonListener(): ValidateJsonRequestListener
    {
        $locator   = new FileLocator([__DIR__]);
        $validator = new JsonValidator($locator, __DIR__);

        return new ValidateJsonRequestListener($validator);
    }

    protected function getControllerEvent(Request $request): ControllerEvent
    {
        $kernel     = $this->getMockBuilder(HttpKernelInterface::class)
                           ->getMock();
        $controller = function ($validJson) {
        };
        $type       = HttpKernelInterface::MAIN_REQUEST;

        return new ControllerEvent($kernel, $controller, $request, $type);
    }
}
