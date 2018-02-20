<?php

namespace ScoutElastic\Tests\Builders;

use ScoutElastic\Builders\FilterBuilder;

class FilterBuilderTest extends AbstractBuilderTest
{
    protected function initBuilder()
    {
        $this->builder = $this
            ->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testWhereEq()
    {
        $this
            ->builder
            ->where('foo', 0)
            ->where('bar', '=', 1);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['foo' => 0]],
                    ['term' => ['bar' => 1]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereNotEq()
    {
        $this
            ->builder
            ->where('foo', '!=', 'bar');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['term' => ['foo' => 'bar']]
                ]
            ],
            $this->builder->wheres
        );
    }

    public function testWhereGt()
    {
        $this
            ->builder
            ->where('foo', '>', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gt' => 0]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereGte()
    {
        $this
            ->builder
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereLt()
    {
        $this
            ->builder
            ->where('foo', '<', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['lt' => 0]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereLte()
    {
        $this
            ->builder
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereIn()
    {
        $this
            ->builder
            ->whereIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [
                    ['terms' => ['foo' => [0, 1]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereNotIn()
    {
        $this
            ->builder
            ->whereNotIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['terms' => ['foo' => [0, 1]]]
                ]
            ],
            $this->builder->wheres
        );
    }

    public function testWhereBetween()
    {
        $this
            ->builder
            ->whereBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereNotBetween()
    {
        $this
            ->builder
            ->whereNotBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]]
                ]
            ],
            $this->builder->wheres
        );
    }

    public function testWhereExists()
    {
        $this
            ->builder
            ->whereExists('foo');

        $this->assertEquals(
            [
                'must' => [
                    ['exists' => ['field' => 'foo']]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereNotExists()
    {
        $this
            ->builder
            ->whereNotExists('foo');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['exists' => ['field' => 'foo']]
                ]
            ],
            $this->builder->wheres
        );
    }

    public function testWhereRegexp()
    {
        $this
            ->builder
            ->whereRegexp('foo', '.*')
            ->whereRegexp('bar', '^test.*', 'EMPTY|NONE');

        $this->assertEquals(
            [
                'must' => [
                    ['regexp' => ['foo' => ['value' => '.*', 'flags' => 'ALL']]],
                    ['regexp' => ['bar' => ['value' => '^test.*', 'flags' => 'EMPTY|NONE']]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhen()
    {
        $this
            ->builder
            ->when(
                false,
                function (FilterBuilder $builder) {
                    return $builder->where('case0', 0);
                }
            )
            ->when(
                false,
                function (FilterBuilder $builder) {
                    return $builder->where('case1', 1);
                },
                function (FilterBuilder $builder) {
                    return $builder->where('case2', 2);
                }
            )
            ->when(
                true,
                function (FilterBuilder $builder) {
                    return $builder->where('case3', 3);
                }
            );

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['case2' => 2]],
                    ['term' => ['case3' => 3]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereGeoDistance()
    {
        $this
            ->builder
            ->whereGeoDistance('foo', [-20, 30], '10m');

        $this->assertEquals(
            [
                'must' => [
                    ['geo_distance' => ['distance' => '10m', 'foo' => [-20, 30]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereGeoBoundingBox()
    {
        $this
            ->builder
            ->whereGeoBoundingBox('foo', ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_bounding_box' => ['foo' => ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testWhereGeoPolygon()
    {
        $this
            ->builder
            ->whereGeoPolygon('foo', [[-70, 40],[-80, 30],[-90, 20]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_polygon' => ['foo' => ['points' => [[-70, 40],[-80, 30],[-90, 20]]]]]
                ],
                'must_not' => []
            ],
            $this->builder->wheres
        );
    }

    public function testOrderBy()
    {
        $this
            ->builder
            ->orderBy('foo')
            ->orderBy('bar', 'DESC');

        $this->assertEquals(
            [
                ['foo' => 'asc'],
                ['bar' => 'desc'],
            ],
            $this->builder->orders
        );
    }

    public function testWith()
    {
        $this
            ->builder
            ->with('RelatedModel');

        $this->assertEquals(
            'RelatedModel',
            $this->builder->with
        );
    }

    public function testFrom()
    {
        $this->assertEquals(
            0,
            $this->builder->offset
        );

        $this
            ->builder
            ->from(100);

        $this->assertEquals(
            100,
            $this->builder->offset
        );
    }
}