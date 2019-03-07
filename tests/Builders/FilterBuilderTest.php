<?php

namespace ScoutElastic\Tests\Builders;

use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Tests\AbstractTestCase;
use ScoutElastic\Tests\Dependencies\Model;

class FilterBuilderTest extends AbstractTestCase
{
    use Model;

    public function testCreationWithSoftDelete()
    {
        $builder = new FilterBuilder($this->mockModel(), null, true);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            '__soft_deleted' => 0,
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testCreationWithoutSoftDelete()
    {
        $builder = new FilterBuilder($this->mockModel(), null, false);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereEq()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', 0)
            ->where('bar', '=', 1);

        $this->assertEquals(
            [
                'must' => [
                    ['term' => ['foo' => 0]],
                    ['term' => ['bar' => 1]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotEq()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '!=', 'bar');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['term' => ['foo' => 'bar']],
                ],
            ],
            $builder->wheres
        );
    }

    public function testWhereGt()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gt' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGte()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereLt()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '<', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['lt' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereLte()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->where('foo', '>=', 0);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereIn()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [
                    ['terms' => ['foo' => [0, 1]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotIn()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotIn('foo', [0, 1]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['terms' => ['foo' => [0, 1]]],
                ],
            ],
            $builder->wheres
        );
    }

    public function testWhereBetween()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotBetween()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotBetween('foo', [0, 10]);

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['range' => ['foo' => ['gte' => 0, 'lte' => 10]]],
                ],
            ],
            $builder->wheres
        );
    }

    public function testWhereExists()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereExists('foo');

        $this->assertEquals(
            [
                'must' => [
                    ['exists' => ['field' => 'foo']],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereNotExists()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereNotExists('foo');

        $this->assertEquals(
            [
                'must' => [],
                'must_not' => [
                    ['exists' => ['field' => 'foo']],
                ],
            ],
            $builder->wheres
        );
    }

    public function testWhereRegexp()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereRegexp('foo', '.*')
            ->whereRegexp('bar', '^test.*', 'EMPTY|NONE');

        $this->assertEquals(
            [
                'must' => [
                    ['regexp' => ['foo' => ['value' => '.*', 'flags' => 'ALL']]],
                    ['regexp' => ['bar' => ['value' => '^test.*', 'flags' => 'EMPTY|NONE']]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhen()
    {
        $builder = (new FilterBuilder($this->mockModel()))
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
                    ['term' => ['case3' => 3]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoDistance()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoDistance('foo', [-20, 30], '10m');

        $this->assertEquals(
            [
                'must' => [
                    ['geo_distance' => ['distance' => '10m', 'foo' => [-20, 30]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoBoundingBox()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoBoundingBox('foo', ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_bounding_box' => ['foo' => ['top_left' => [-5, 10], 'bottom_right' => [-20, 30]]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoPolygon()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoPolygon('foo', [[-70, 40], [-80, 30], [-90, 20]]);

        $this->assertEquals(
            [
                'must' => [
                    ['geo_polygon' => ['foo' => ['points' => [[-70, 40], [-80, 30], [-90, 20]]]]],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testWhereGeoShape()
    {
        $shape = [
            'type' => 'circle',
            'radius' => '1km',
            'coordinates' => [
                4.89994,
                52.37815,
            ],
        ];

        $builder = (new FilterBuilder($this->mockModel()))
            ->whereGeoShape('foo', $shape);

        $this->assertEquals(
            [
                'must' => [
                    [
                        'geo_shape' => [
                            'foo' => [
                                'shape' => $shape,
                            ],
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testOrderBy()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->orderBy('foo')
            ->orderBy('bar', 'DESC');

        $this->assertEquals(
            [
                ['foo' => 'asc'],
                ['bar' => 'desc'],
            ],
            $builder->orders
        );
    }

    public function testWith()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->with('RelatedModel');

        $this->assertEquals(
            'RelatedModel',
            $builder->with
        );
    }

    public function testFrom()
    {
        $builder = new FilterBuilder($this->mockModel());

        $this->assertEquals(
            0,
            $builder->offset
        );

        $builder->from(100);

        $this->assertEquals(
            100,
            $builder->offset
        );
    }

    public function testCollapse()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->collapse('foo');

        $this->assertEquals(
            'foo',
            $builder->collapse
        );
    }

    public function testSelect()
    {
        $builder = (new FilterBuilder($this->mockModel()))
            ->select(['foo', 'bar']);

        $this->assertEquals(
            ['foo', 'bar'],
            $builder->select
        );
    }

    public function testWithTrashed()
    {
        $builder = (new FilterBuilder($this->mockModel(), null, true))
            ->withTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }

    public function testOnlyTrashed()
    {
        $builder = (new FilterBuilder($this->mockModel(), null, true))
            ->onlyTrashed()
            ->where('foo', 'bar');

        $this->assertEquals(
            [
                'must' => [
                    [
                        'term' => [
                            '__soft_deleted' => 1,
                        ],
                    ],
                    [
                        'term' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
                'must_not' => [],
            ],
            $builder->wheres
        );
    }
}
