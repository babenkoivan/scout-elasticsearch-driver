<?php

namespace ScoutElastic\Tests\Payloads;

use PHPUnit\Framework\TestCase;
use ScoutElastic\Payloads\RawPayload;

class RawPayloadTest extends TestCase
{
    public function testSet()
    {
        $payload = (new RawPayload())
            ->set('foo.bar', 10);

        $this->assertEquals(
            ['foo' => ['bar' => 10]],
            $payload->get()
        );
    }

    public function testSetIfNotEmpty()
    {
        $payload = (new RawPayload())
            ->setIfNotEmpty('null', null)
            ->setIfNotEmpty('false', false)
            ->setIfNotEmpty('zero', 0)
            ->setIfNotEmpty('empty_array', [])
            ->setIfNotEmpty('empty_string', '')
            ->setIfNotEmpty('foo', 'bar');

        $this->assertEquals(
            ['foo' => 'bar'],
            $payload->get()
        );
    }

    public function testHas()
    {
        $payload = (new RawPayload())
            ->set('foo.bar', 100);

        $this->assertTrue($payload->has('foo'));
        $this->assertTrue($payload->has('foo.bar'));
        $this->assertFalse($payload->has('not_exist'));
    }

    public function testAdd()
    {
        $payload = (new RawPayload())
            ->set('foo', 0)
            ->add('foo', 1);

        $this->assertEquals(
            ['foo' => [0, 1]],
            $payload->get()
        );
    }

    public function testAddIfNotEmpty()
    {
        $payload = (new RawPayload())
            ->addIfNotEmpty('foo', 0)
            ->addIfNotEmpty('foo', 1);

        $this->assertEquals(
            ['foo' => [1]],
            $payload->get()
        );
    }

    public function testGet()
    {
        $payload = (new RawPayload())
            ->set('foo.bar', 0);

        $this->assertEquals(
            ['bar' => 0],
            $payload->get('foo')
        );

        $this->assertEquals(
            ['foo' => ['bar' => 0]],
            $payload->get()
        );
    }
}