<?php

namespace Tests;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Mrsuh\JsonValidationBundle\EventListener\JsonValidationRequestListener;
use Mrsuh\JsonValidationBundle\Exception\JsonValidationRequestException;
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class ValidateJsonListenerTest extends TestCase
{
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
        $request->attributes->set(sprintf('_%s', ValidateJsonRequest::ALIAS), $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertFalse($request->attributes->has('validJson'));
    }

    public function testInvalidJson()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);

        $request = Request::create('/', 'POST', [], [], [], [], '{invalid');
        $request->attributes->set(sprintf('_%s', ValidateJsonRequest::ALIAS), $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $this->expectException(JsonValidationRequestException::class);
        $listener->onKernelController($event);
    }

    public function testValidJson()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);

        $request = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set(sprintf('_%s', ValidateJsonRequest::ALIAS), $annotation);

        $event    = $this->getControllerEvent($request);
        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertEquals($request->attributes->get('validJson')->test, 'hello');
    }

    public function testValidJsonArray()
    {
        $annotation = new ValidateJsonRequest(['path' => 'schema-simple.json']);
        $request    = Request::create('/', 'POST', [], [], [], [], '{"test": "hello"}');
        $request->attributes->set(sprintf('_%s', ValidateJsonRequest::ALIAS), $annotation);

        $kernel     = $this->getMockBuilder(HttpKernelInterface::class)
                           ->getMock();
        $controller = function (array $validJson) {
        };
        $type       = HttpKernelInterface::MASTER_REQUEST;
        $event      = new ControllerEvent($kernel, $controller, $request, $type);

        $listener = $this->getValidateJsonListener();

        $listener->onKernelController($event);

        $this->assertTrue($request->attributes->has('validJson'));
        $this->assertTrue(is_array($request->attributes->get('validJson')));
        $this->assertEquals($request->attributes->get('validJson')['test'], 'hello');
    }

    protected function getValidateJsonListener(): JsonValidationRequestListener
    {
        $locator   = new FileLocator([__DIR__]);
        $logger    = new Logger();
        $validator = new JsonValidator($logger, $locator);

        return new JsonValidationRequestListener($validator);
    }

    protected function getControllerEvent(Request $request): ControllerEvent
    {
        $kernel     = $this->getMockBuilder(HttpKernelInterface::class)
                           ->getMock();
        $controller = function ($validJson) {
        };
        $type       = HttpKernelInterface::MASTER_REQUEST;

        return new ControllerEvent($kernel, $controller, $request, $type);
    }
}
