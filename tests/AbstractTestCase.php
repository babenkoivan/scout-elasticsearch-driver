<?php

namespace ScoutElastic\Tests;

use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Config::reset();
    }
}
