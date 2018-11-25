<?php

namespace ScoutElastic\Builders;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Builder;

class FilterBuilder extends Builder
{
    /**
     * @var array
     */
    public $wheres = [
        'must' => [],
        'must_not' => []
    ];

    /**
     * @var array|string
     */
    public $with;

    /**
     * @var int
     */
    public $offset;

    /**
     * @var string
     */
    public $collapse;

    /**
     * @var array
     */
    public $select = [];

    /**
     * @param Model $model
     * @param callable|null $callback
     * @param bool $softDelete
     */
    public function __construct(Model $model, $callback = null, $softDelete = false)
    {
        $this->model = $model;
        $this->callback = $callback;

        if ($softDelete) {
            $this->wheres['must'][] = [
                'term' => [
                    '__soft_deleted' => 0
                ]
            ];
        }
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html Term query
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     * @param string $field Field name
     * @param mixed $value Scalar value or an array
     * @return $this
     */
    public function where($field, $value)
    {
        $args = func_get_args();

        if (count($args) == 3) {
            list($field, $operator, $value) = $args;
        } else {
            $operator = '=';
        }

        switch ($operator) {
            case '=':
                $this->wheres['must'][] = [
                    'term' => [
                        $field => $value
                    ]
                ];
                break;

            case '>':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gt' => $value
                        ]
                    ]
                ];
                break;

            case '<':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lt' => $value
                        ]
                    ]
                ];
                break;

            case '>=':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'gte' => $value
                        ]
                    ]
                ];
                break;

            case '<=':
                $this->wheres['must'][] = [
                    'range' => [
                        $field => [
                            'lte' => $value
                        ]
                    ]
                ];
                break;

            case '!=':
            case '<>':
                $this->wheres['must_not'][] = [
                    'term' => [
                        $field => $value
                    ]
                ];
                break;
        }

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereIn($field, array $value)
    {
        $this->wheres['must'][] = [
            'terms' => [
                $field => $value
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html Terms query
     *
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereNotIn($field, array $value)
    {
        $this->wheres['must_not'][] = [
            'terms' => [
                $field => $value
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereBetween($field, array $value)
    {
        $this->wheres['must'][] = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1]
                ]
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html Range query
     *
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereNotBetween($field, array $value)
    {
        $this->wheres['must_not'][] = [
            'range' => [
                $field => [
                    'gte' => $value[0],
                    'lte' => $value[1]
                ]
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param string $field
     * @return $this
     */
    public function whereExists($field)
    {
        $this->wheres['must'][] = [
            'exists' => [
                'field' => $field
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html Exists query
     *
     * @param string $field
     * @return $this
     */
    public function whereNotExists($field)
    {
        $this->wheres['must_not'][] = [
            'exists' => [
                'field' => $field
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html Regexp query
     *
     * @param string $field
     * @param string $value
     * @param string $flags
     * @return $this
     */
    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        $this->wheres['must'][] = [
            'regexp' => [
                $field => [
                    'value' => $value,
                    'flags' => $flags
                ]
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-distance-query.html Geo distance query
     *
     * @param string $field
     * @param string|array $value
     * @param int|string $distance
     * @return $this
     */
    public function whereGeoDistance($field, $value, $distance)
    {
        $this->wheres['must'][] = [
            'geo_distance' => [
                'distance' => $distance,
                $field => $value
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html Geo bounding box query
     *
     * @param string $field
     * @param array $value
     * @return $this
     */
    public function whereGeoBoundingBox($field, array $value)
    {
        $this->wheres['must'][] = [
            'geo_bounding_box' => [
                $field => $value
            ]
        ];

        return $this;
    }

    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-polygon-query.html Geo polygon query
     *
     * @param string $field
     * @param array $points
     * @return $this
     */
    public function whereGeoPolygon($field, array $points)
    {
        $this->wheres['must'][] = [
            'geo_polygon' => [
                $field => [
                    'points' => $points
                ]
            ]
        ];

        return $this;
    }
    
    /**
     * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/querying-geo-shapes.html Querying Geo Shapes
     *
     * @param string $field
     * @param array $shape
     * @return $this
     */
    public function whereGeoShape($field, array $shape)
    {
        $this->wheres['must'][] = [
            'geo_shape' => [
                $field => [
                    'shape' => $shape
                ]
            ]
        ];

        return $this;
    }
    
    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function orderBy($field, $direction = 'asc')
    {
        $this->orders[] = [
            $field => strtolower($direction) == 'asc' ? 'asc' : 'desc'
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function explain()
    {
        return $this
            ->engine()
            ->explain($this);
    }

    /**
     * @return array
     */
    public function profile()
    {
        return $this
            ->engine()
            ->profile($this);
    }

    /**
     * @return array
     */
    public function buildPayload()
    {
        return $this
            ->engine()
            ->buildSearchQueryPayloadCollection($this);
    }

    /**
     * @param array|string $relations
     * @return $this
     */
    public function with($relations)
    {
        $this->with = $relations;

        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function from($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @param string $field
     * @return $this
     */
    public function collapse(string $field)
    {
        $this->collapse = $field;

        return $this;
    }

    /**
     * @param mixed $fields
     * @return $this
     */
    public function select($fields)
    {
        $this->select = array_merge(
            $this->select,
            array_wrap($fields)
        );

        return $this;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this
            ->engine()
            ->count($this);
    }

    /**
     * @inheritdoc
     */
    public function withTrashed()
    {
        $this->wheres['must'] = collect($this->wheres['must'])
            ->filter(function ($item) {
                return array_get($item, 'term.__soft_deleted') !== 0;
            })
            ->values()
            ->all();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function onlyTrashed()
    {
        return tap($this->withTrashed(), function () {
            $this->wheres['must'][] = ['term' => ['__soft_deleted' => 1]];
        });
    }
}
