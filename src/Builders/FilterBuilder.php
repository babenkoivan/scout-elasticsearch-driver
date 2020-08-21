<?php

namespace ScoutElastic\Builders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Laravel\Scout\Builder;

class FilterBuilder extends Builder
{
    /**
     * The condition array.
     *
     * @var array
     */
    public $wheres = [
        'must' => [],
        'must_not' => [],
        'should' => [],
    ];

    /**
     * The with array.
     *
     * @var array|string
     */
    public $with;

    /**
     * The offset.
     *
     * @var int
     */
    public $offset;

    /**
     * The collapse parameter.
     *
     * @var string
     */
    public $collapse;

    /**
     * The select array.
     *
     * @var array
     */
    public $select = [];

    /**
     * The min_score parameter.
     *
     * @var string
     */
    public $minScore;

    /**
     * FilterBuilder constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  callable|null  $callback
     * @param  bool  $softDelete
     * @return void
     */
    public function __construct(Model $model, $callback = null, $softDelete = false)
    {
        $this->model = $model;
        $this->callback = $callback;

        if ($softDelete) {
            $this->wheres['must'][] = [
                'term' => [
                    '__soft_deleted' => 0,
                ],
            ];
        }
    }

    /**
     * Add a where condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Term query
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     *
     * @param string|\Closure $field
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this|FilterBuilder
     */
    public function where($field, $operator = null, $value = null, $boolean = 'must')
    {
        if ($field instanceof \Closure) {
            return $this->whereNested($field, $boolean);
        }

        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        switch ($operator) {
            case '=':
                $this->wheres[$boolean][] = [
                    'term' => [
                        $field => $value,
                    ],
                ];
                break;

            case '>':
                $this->wheres[$boolean][] = [
                    'range' => [
                        $field => [
                            'gt' => $value,
                        ],
                    ],
                ];
                break;

            case '<':
                $this->wheres[$boolean][] = [
                    'range' => [
                        $field => [
                            'lt' => $value,
                        ],
                    ],
                ];
                break;

            case '>=':
                $this->wheres[$boolean][] = [
                    'range' => [
                        $field => [
                            'gte' => $value,
                        ],
                    ],
                ];
                break;

            case '<=':
                $this->wheres[$boolean][] = [
                    'range' => [
                        $field => [
                            'lte' => $value,
                        ],
                    ],
                ];
                break;

            case '!=':
            case '<>':
                $term = [
                    'term' => [
                        $field => $value,
                    ],
                ];
                $this->setNegativeCondition($term, $boolean);
                break;
        }

        return $this;
    }

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @return $this|\Illuminate\Database\Query\Builder
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'should');
    }

    /**
     * @param Closure $callback
     * @param string $boolean
     * @return $this
     */
    public function whereNested(\Closure $callback, $boolean = 'must')
    {
        /** @var $filter FilterBuilder */
        call_user_func($callback, $filter = $this->model::search('*'));

        $payload = $filter->buildPayload();
        $this->wheres[$boolean][] = $payload[0]['body']['query']['bool']['filter'];

        return $this;
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        }

        return [$value, $operator];
    }

    /**
     * @param $condition
     * @param string $boolean
     */
    public function setNegativeCondition($condition, $boolean = 'must')
    {
        if ($boolean == 'should') {
            $cond['bool']['must_not'][] = $condition;

            $this->wheres[$boolean][] = $cond;
        } else {
            $this->wheres['must_not'][] = $condition;
        }
    }

    /**
     * Add a whereIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array  $value
     * @param string $boolean
     * @return $this
     */
    public function whereIn($field, array $value, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'terms' => [
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     * @return $this
     */
    public function orWhereIn($field, array $value)
    {
        return $this->whereIn($field, $value, 'should');
    }

    /**
     * Add a whereNotIn condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param  string  $field
     * @param  array  $value
     * @param string $boolean
     * @return $this
     */
    public function whereNotIn($field, array $value, $boolean = 'must')
    {
        $term = [
            'terms' => [
                $field => $value,
            ],
        ];
        $this->setNegativeCondition($term, $boolean);

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     * @return $this
     */
    public function orWhereNotIn($field, array $value)
    {
        return $this->whereNotIn($field, $value, 'should');

        return $this;
    }

    /**
     * Add a whereBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param  string  $field
     * @param  array  $value
     * @param string $boolean
     * @return $this
     */
    public function whereBetween($field, array $value, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     * @return $this
     */
    public function orWhereBetween($field, array $value)
    {
        return $this->whereBetween($field, $value);
    }

    /**
     * Add a whereNotBetween condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param  string  $field
     * @param  array  $value
     * @param string $boolean
     * @return $this
     */
    public function whereNotBetween($field, array $value, $boolean = 'must')
    {
        $term = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1],
                ],
            ],
        ];
        $this->setNegativeCondition($term, $boolean);

        return $this;
    }

    /**
     * @param $field
     * @param array $value
     */
    public function orWhereNotBetween($field, array $value)
    {
        return $this->whereNotBetween($field, $value, 'should');
    }

    /**
     * Add a whereExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param  string  $field
     * @param string $boolean
     * @return $this
     */
    public function whereExists($field, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'exists' => [
                'field' => $field,
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @return $this
     */
    public function orWhereExists($field)
    {
        return $this->whereExists($field, 'should');
    }

    /**
     * Add a whereNotExists condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param  string  $field
     * @param string $boolean
     * @return $this
     */
    public function whereNotExists($field, $boolean = 'must')
    {
        $term = [
            'exists' => [
                'field' => $field,
            ],
        ];
        $this->setNegativeCondition($term, $boolean);

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param string $field
     * @return $this|FilterBuilder
     */
    public function orWhereNotExists($field)
    {
        return $this->whereNotExists($field, 'should');
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html Match query
     *
     * @param string $field
     * @param string $value
     * @param string $boolean
     * @return $this
     */
    public function whereMatch($field, $value, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'match' => [
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function orWhereMatch($field, $value)
    {
        return $this->whereMatch($field, $value, 'should');
    }


    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html Match query
     *
     * @param string $field
     * @param string $value
     * @param string $boolean
     * @return $this
     */
    public function whereNotMatch($field, $value, $boolean = 'must')
    {
        $term = [
            'match' => [
                $field => $value,
            ],
        ];
        $this->setNegativeCondition($term, $boolean);

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function orWhereNotMatch($field, $value)
    {
        return $this->whereNotMatch($field, $value, 'should');
    }

    /**
     * Add a whereRegexp condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html Regexp query
     *
     * @param  string  $field
     * @param  string  $value
     * @param  string  $flags
     * @param string $boolean
     * @return $this
     */
    public function whereRegexp($field, $value, $flags = 'ALL', $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'regexp' => [
                $field => [
                    'value' => $value,
                    'flags' => $flags,
                ],
            ],
        ];

        return $this;
    }

    public function orWhereRegexp($field, $value, $flags = 'ALL')
    {
        return $this->whereRegexp($field, $value, $flags, 'should');
    }

    /**
     * Add a whereGeoDistance condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html Geo distance query
     *
     * @param  string  $field
     * @param  string|array  $value
     * @param  int|string  $distance
     * @param string $boolean
     * @return $this
     */
    public function whereGeoDistance($field, $value, $distance, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'geo_distance' => [
                'distance' => $distance,
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param $distance
     * @return $this
     */
    public function orWhereGeoDistance($field, $value, $distance)
    {
        return $this->whereGeoDistance($field, $value, $distance, 'should');
    }

    /**
     * Add a whereGeoBoundingBox condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html Geo bounding box query
     *
     * @param  string  $field
     * @param  array  $value
     * @param string $boolean
     * @return $this
     */
    public function whereGeoBoundingBox($field, array $value, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'geo_bounding_box' => [
                $field => $value,
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function orWhereGeoBoundingBox($field, $value)
    {
        return $this->whereGeoBoundingBox($field, $value, 'should');
    }

    /**
     * Add a whereGeoPolygon condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html Geo polygon query
     *
     * @param  string  $field
     * @param  array  $points
     * @param string $boolean
     * @return $this
     */
    public function whereGeoPolygon($field, array $points, $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'geo_polygon' => [
                $field => [
                    'points' => $points,
                ],
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param array $points
     * @return $this
     */
    public function orWhereGeoPolygon($field, array $points)
    {
        return $this->whereGeoPolygon($field, $points, 'should');
    }

    /**
     * Add a whereGeoShape condition.
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html Querying Geo Shapes
     *
     * @param  string  $field
     * @param  array  $shape
     * @param  string  $relation
     * @param string $boolean
     * @return $this
     */
    public function whereGeoShape($field, array $shape, $relation = 'INTERSECTS', $boolean = 'must')
    {
        $this->wheres[$boolean][] = [
            'geo_shape' => [
                $field => [
                    'shape' => $shape,
                    'relation' => $relation,
                ],
            ],
        ];

        return $this;
    }

    /**
     * @param $field
     * @param array $shape
     * @param string $relation
     * @return $this
     */
    public function orWhereGeoShape($field, array $shape, $relation = 'INTERSECTS')
    {
        return $this->whereGeoShape($field, $shape, $relation, 'should');
    }

    /**
     * Add a orderBy clause.
     *
     * @param  string  $field
     * @param  string  $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->orders[] = [
            $field => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Add a raw order clause.
     *
     * @param array $payload
     * @return $this
     */
    public function orderRaw(array $payload)
    {
        $this->orders[] = $payload;

        return $this;
    }

    /**
     * Explain the request.
     *
     * @return array
     */
    public function explain()
    {
        return $this
            ->engine()
            ->explain($this);
    }

    /**
     * Profile the request.
     *
     * @return array
     */
    public function profile()
    {
        return $this
            ->engine()
            ->profile($this);
    }

    /**
     * Build the payload.
     *
     * @return array
     */
    public function buildPayload()
    {
        return $this
            ->engine()
            ->buildSearchQueryPayloadCollection($this);
    }

    /**
     * Eager load some some relations.
     *
     * @param  array|string  $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->with = $relations;

        return $this;
    }

    /**
     * Set the query offset.
     *
     * @param  int  $offset
     * @return $this
     */
    public function from($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get()
    {
        $collection = parent::get();

        if (isset($this->with) && $collection->count() > 0) {
            $collection->load($this->with);
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $pageName = 'page', $page = null)
    {
        $paginator = parent::paginate($perPage, $pageName, $page);

        if (isset($this->with) && $paginator->total() > 0) {
            $paginator
                ->getCollection()
                ->load($this->with);
        }

        return $paginator;
    }

    /**
     * Collapse by a field.
     *
     * @param  string  $field
     * @return $this
     */
    public function collapse(string $field)
    {
        $this->collapse = $field;

        return $this;
    }

    /**
     * Select one or many fields.
     *
     * @param  mixed  $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->select = array_merge(
            $this->select,
            Arr::wrap($fields)
        );

        return $this;
    }

    /**
     * Set the min_score on the filter.
     *
     * @param  float  $score
     * @return $this
     */
    public function minScore($score)
    {
        $this->minScore = $score;

        return $this;
    }

    /**
     * Get the count.
     *
     * @return int
     */
    public function count()
    {
        return $this
            ->engine()
            ->count($this);
    }

    /**
     * {@inheritdoc}
     */
    public function withTrashed()
    {
        $this->wheres['must'] = collect($this->wheres['must'])
            ->filter(function ($item) {
                return Arr::get($item, 'term.__soft_deleted') !== 0;
            })
            ->values()
            ->all();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function onlyTrashed()
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['must'][] = ['term' => ['__soft_deleted' => 1]];
        });
    }
}
