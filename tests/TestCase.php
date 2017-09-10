<?php

namespace ScoutElastic\Tests;

use Mockery;
use PHPUnit_Framework_TestCase;
use ScoutElastic\Facades\ElasticClient;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function tearDown()
    {
        Mockery::close();

        parent::tearDown();
    }

    protected function setUp()
    {
        app()->instance('config', new class() {
            public function get($key)
            {
                return '';
            }
        });

        parent::setUp();
    }

    protected function mockClient()
    {
        return Mockery::mock('alias:' . ElasticClient::class);
    }
}