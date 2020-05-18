<?php

namespace Tests;

use Mrsuh\JsonValidationBundle\Annotation\ValidateJsonRequest;
use Mrsuh\JsonValidationBundle\EventListener\ValidateJsonExceptionListener;
use Mrsuh\JsonValidationBundle\Exception\JsonValidationRequestException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\{Request, Response};
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Log\Logger;

class ValidateJsonExceptionListenerTest extends TestCase
{
    public function testNonJsonValidationException()
    {
        $event    = $this->getEvent(new \RuntimeException('Not JsonValidationException'));
        $resource = fopen('php://memory', 'r+');
        $listener = new ValidateJsonExceptionListener(new Logger(LogLevel::DEBUG, $resource));

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
        $this->assertFalse($this->hasResourceStr($resource, 'Json request validation'));
    }

    public function testEmptyErrors()
    {
        $event    = $this->getEvent($this->createJsonValidationRequestException([]));
        $resource = fopen('php://memory', 'r+');
        $listener = new ValidateJsonExceptionListener(new Logger(LogLevel::DEBUG, $resource));

        $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertTrue($event->getResponse()->headers->contains('Content-Type', 'application/problem+json'));

        $json = json_decode($event->getResponse()->getContent());
        $this->assertEquals([], $json->errors);
        $this->assertEquals(400, $json->status);
        $this->assertEquals('Unable to parse/validate JSON', $json->title);
        $this->assertEquals('There was a problem with the JSON that was sent with the request', $json->detail);
        $this->assertTrue($this->hasResourceStr($resource, 'Json request validation'));
    }

    public function testMessageOnlyError()
    {
        $event = $this->getEvent($this->createJsonValidationRequestException([['message' => 'Test message'],]));

        $resource = fopen('php://memory', 'r+');
        $listener = new ValidateJsonExceptionListener(new Logger(LogLevel::DEBUG, $resource));
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([['message' => 'Test message']], $json['errors']);
        $this->assertTrue($this->hasResourceStr($resource, 'Json request validation'));
    }

    public function testConstraintError()
    {
        $event = $this->getEvent($this->createJsonValidationRequestException([
            [
                'constraint' => 'a',
                'property'   => 'b',
                'pointer'    => 'c',
                'message'    => 'd',
            ]
        ]));

        $listener = new ValidateJsonExceptionListener(new Logger());
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([
            [
                'constraint' => 'a',
                'property'   => 'b',
                'pointer'    => 'c',
                'message'    => 'd',
            ]
        ], $json['errors']);
    }

    public function testMixedErrors()
    {
        $event = $this->getEvent($this->createJsonValidationRequestException([
            ['message' => 'Test message'],
            [
                'constraint' => 'a',
                'property'   => 'b',
                'pointer'    => 'c',
                'message'    => 'd',
            ]
        ]));

        $listener = new ValidateJsonExceptionListener(new Logger());
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([
            ['message' => 'Test message'],
            [
                'constraint' => 'a',
                'property'   => 'b',
                'pointer'    => 'c',
                'message'    => 'd',
            ]
        ], $json['errors']);
    }

    protected function getEvent(\Throwable $exception): ExceptionEvent
    {
        $kernel      = $this->getMockBuilder(HttpKernelInterface::class)
                            ->getMock();
        $request     = Request::create('/');
        $requestType = HttpKernelInterface::MASTER_REQUEST;

        return new ExceptionEvent($kernel, $request, $requestType, $exception);
    }

    protected function createJsonValidationRequestException(array $errors = [])
    {
        return new JsonValidationRequestException(Request::create('/'), '/', $errors);
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
