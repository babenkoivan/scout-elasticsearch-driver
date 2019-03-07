<?php

namespace ScoutElastic\Tests;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();

        Config::reset();
    }
}
