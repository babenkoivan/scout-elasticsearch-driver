<?php

namespace ScoutElastic\Tests\Builders;

use PHPUnit\Framework\TestCase;
use Laravel\Scout\Builder;

abstract class AbstractBuilderTest extends TestCase
{
    /**
     * @var Builder
     */
    protected $builder;

    abstract protected function initBuilder();

    protected function setUp()
    {
        $this->initBuilder();
    }
}