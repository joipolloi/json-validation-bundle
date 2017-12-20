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
use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Symfony\Component\HttpKernel\Tests\Fixtures\KernelForTest;
use Symfony\Component\HttpFoundation\{Request,Response};
use JoiPolloi\Bundle\JsonValidationBundle\EventListener\JsonValidationExceptionListener,
    JoiPolloi\Bundle\JsonValidationBundle\Exception\JsonValidationException;

/**
 * JSON validation exception listener tests
 *
 * @author John Noel <john.noel@joipolloi.com>
 * @package JsonValidationExtension
 */
class JsonValidationExceptionListenerTest extends TestCase
{
    public function testNonJsonValidationException()
    {
        $event = $this->getEvent(new \RuntimeException('Not JsonValidationException'));
        $listener = new JsonValidationExceptionListener();

        $listener->onKernelException($event);

        $this->assertNull($event->getResponse());
    }

    public function testEmptyErrors()
    {
        $event = $this->getEvent(new JsonValidationException('', []));
        $listener = new JsonValidationExceptionListener();

        $listener->onKernelException($event);

        $this->assertInstanceOf(Response::class, $event->getResponse());
        $this->assertTrue($event->getResponse()->headers->contains('Content-Type', 'application/problem+json'));

        $json = json_decode($event->getResponse()->getContent());
        $this->assertEquals([], $json->errors);
        $this->assertEquals(400, $json->status);
        $this->assertEquals('Unable to parse/validate JSON', $json->title);
        $this->assertEquals('There was a problem with the JSON that was sent with the request', $json->detail);
    }

    public function testMessageOnlyError()
    {
        $event = $this->getEvent(new JsonValidationException('', [
            [ 'message' => 'Test message' ],
        ]));

        $listener = new JsonValidationExceptionListener();
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([ [ 'message' => 'Test message' ] ], $json['errors']);
    }

    public function testContraintError()
    {
        $event = $this->getEvent(new JsonValidationException('', [
            [
                'constraint' => 'a',
                'property' => 'b',
                'pointer' => 'c',
                'message' => 'd',
            ]
        ]));

        $listener = new JsonValidationExceptionListener();
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([
            [
                'constraint' => 'a',
                'property' => 'b',
                'pointer' => 'c',
                'message' => 'd',
            ]
        ], $json['errors']);
    }

    public function testMixedErrors()
    {
        $event = $this->getEvent(new JsonValidationException('', [
            [ 'message' => 'Test message' ],
            [
                'constraint' => 'a',
                'property' => 'b',
                'pointer' => 'c',
                'message' => 'd',
            ]
        ]));

        $listener = new JsonValidationExceptionListener();
        $listener->onKernelException($event);

        $json = json_decode($event->getResponse()->getContent(), true);

        $this->assertEquals([
            [ 'message' => 'Test message' ],
            [
                'constraint' => 'a',
                'property' => 'b',
                'pointer' => 'c',
                'message' => 'd',
            ]
        ], $json['errors']);
    }

    protected function getEvent(\Exception $exception) : GetResponseForExceptionEvent
    {
        $kernel = new KernelForTest('test', true);
        $request = Request::create('/');
        $requestType = HttpKernelInterface::MASTER_REQUEST;

        return new GetResponseForExceptionEvent($kernel, $request, $requestType, $exception);
    }
}
