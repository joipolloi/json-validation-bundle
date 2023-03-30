<?php

namespace Tests;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonResponse;
use Mrsuh\JsonValidationBundle\EventListener\ValidateJsonResponseListener;
use Mrsuh\JsonValidationBundle\JsonValidator\JsonValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class ValidateJsonResponseListenerTest extends TestCase
{
    public function testInvalidStatus()
    {
        $annotation = new ValidateJsonResponse(['path' => 'schema-simple.json', 'statuses' => [200]]);

        $request  = Request::create('/');
        $response = new Response('', 201);

        $request->attributes->set(ValidateJsonResponse::class, $annotation);

        $event = $this->getResponseEvent($request, $response);

        $resource = fopen('php://memory', 'r+');
        $listener = $this->getValidateJsonResponseListener($resource);

        $listener->onKernelResponse($event);

        $this->assertFalse($this->hasResourceStr($resource, 'Json response validation'));
    }

    public function testInvalidJson()
    {
        $annotation = new ValidateJsonResponse(['path' => 'schema-simple.json', 'statuses' => [200]]);

        $request  = Request::create('/');
        $response = new Response('{invalid', 200);

        $request->attributes->set(ValidateJsonResponse::class, $annotation);

        $event = $this->getResponseEvent($request, $response);

        $resource = fopen('php://memory', 'r+');
        $listener = $this->getValidateJsonResponseListener($resource);

        $listener->onKernelResponse($event);

        $this->assertTrue($this->hasResourceStr($resource, 'Json response validation'));
    }

    public function testValidJson()
    {
        $annotation = new ValidateJsonResponse(['path' => 'schema-simple.json', 'statuses' => [200]]);

        $request  = Request::create('/');
        $response = new Response('{"test": "hello"}', 200);

        $request->attributes->set(ValidateJsonResponse::class, $annotation);

        $event = $this->getResponseEvent($request, $response);

        $resource = fopen('php://memory', 'r+');
        $listener = $this->getValidateJsonResponseListener($resource);

        $listener->onKernelResponse($event);

        $this->assertFalse($this->hasResourceStr($resource, 'Json response validation'));
    }

    protected function getValidateJsonResponseListener($resource): ValidateJsonResponseListener
    {
        $locator   = new FileLocator([__DIR__]);
        $validator = new JsonValidator($locator, __DIR__);
        $logger    = new Logger(LogLevel::DEBUG, $resource);

        return new ValidateJsonResponseListener($validator, $logger);
    }

    protected function getResponseEvent(Request $request, Response $response): ResponseEvent
    {
        $kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $type   = HttpKernelInterface::MAIN_REQUEST;

        return new ResponseEvent($kernel, $request, $type, $response);
    }

    /**
     * @param resource $loggerResource
     */
    public function hasResourceStr($loggerResource, string $needle)
    {
        fseek($loggerResource, 0);
        while ($buff = fgets($loggerResource)) {
            if (mb_strpos($buff, $needle) !== false) {
                return true;
            }
        }

        return false;
    }
}
