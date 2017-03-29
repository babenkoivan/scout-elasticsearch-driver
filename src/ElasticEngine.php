<?php

namespace ScoutElastic;

use Config;
use Artisan;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;

class ElasticEngine extends Engine
{
    protected $updateMapping = false;

    public function __construct()
    {
        $this->updateMapping = Config::get('scout_elastic.update_mapping');
    }

    /**
     * @param SearchableModel $model
     * @param mixed $bodyPayload
     * @return array
     */
    protected function buildTypePayload($model, $bodyPayload = null)
    {
        $payload = [
            'index' => $model->getIndexConfigurator()->getName(),
            'type' => $model->searchableAs(),
        ];

        if ($bodyPayload) {
            $payload['body'] = $bodyPayload;
        }

        return $payload;
    }

    /**
     * @param SearchableModel $model
     * @param mixed $bodyPayload
     * @return array
     */
    protected function buildDocumentPayload($model, $bodyPayload = null)
    {
        return array_merge(
            $this->buildTypePayload($model, $bodyPayload),
            ['id' => $model->getKey()]
        );
    }

    protected function buildFilterPayload(Builder $builder)
    {
        $payload = [];

        foreach ($builder->wheres as $where) {
            $must = null;
            $mustNot = null;

            /** @var string $field */
            /** @var string $type */
            /** @var string $operator */
            /** @var mixed $value */
            /** @var bool $not */
            /** @var string $flags */
            extract($where);

            switch ($type) {
                case 'basic':
                    switch ($operator) {
                        case '=':
                            $must = ['term' => [$field => $value]];
                            break;

                        case '>':
                            $must = ['range' => [$field => ['gt' => $value]]];
                            break;

                        case '<';
                            $must = ['range' => [$field => ['lt' => $value]]];
                            break;

                        case '>=':
                            $must = ['range' => [$field => ['gte' => $value]]];
                            break;

                        case '<=':
                            $must = ['range' => [$field => ['lte' => $value]]];
                            break;

                        case '<>':
                            $mustNot = ['term' => [$field => $value]];
                            break;
                    }
                    break;

                case 'in':
                    if ($not) {
                        $mustNot = ['terms' => [$field => $value]];
                    } else {
                        $must = ['terms' => [$field => $value]];
                    }
                    break;

                case 'between':
                    if ($not) {
                        $mustNot = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];
                    } else {
                        $must = ['range' => [$field => ['gte' => $value[0], 'lte' => $value[1]]]];
                    }
                    break;

                case 'exists':
                    if ($not) {
                        $mustNot = ['exists' => ['field' => $field]];
                    } else {
                        $must = ['exists' => ['field' => $field]];
                    }
                    break;

                case 'regexp':
                    $must = ['regexp' => [$field => ['value' => $value, 'flags' => $flags]]];
                    break;
            }

            if ($must || $mustNot) {
                if (!isset($payload['bool'])) {
                    $payload['bool'] = [];
                }

                if ($must) {
                    if (!isset($payload['bool']['must'])) {
                        $payload['bool']['must'] = [];
                    }

                    $payload['bool']['must'][] = $must;
                }

                if ($mustNot) {
                    if (!isset($payload['bool']['must_not'])) {
                        $payload['bool']['must_not'] = [];
                    }

                    $payload['bool']['must_not'][] = $mustNot;
                }
            }
        }

        return $payload;
    }

    protected function buildSearchQueryPayload(Builder $builder, $queryPayload, array $options = [])
    {
        $payload = [
            'query' => [
                'bool' => $queryPayload
            ]
        ];

        if ($filterPayload = $this->buildFilterPayload($builder)) {
            $payload['query']['bool'] = array_merge_recursive(
                $payload['query']['bool'],
                ['filter' => $filterPayload]
            );
        }

        if ($size = isset($options['limit']) ? $options['limit'] : $builder->limit) {
            $payload['size'] = $size;

            if (isset($options['page'])) {
                $payload['from'] = ($options['page'] - 1) * $size;
            }
        }

        if ($orders = $builder->orders) {
            $payload['sort'] = [];

            foreach ($orders as $order) {
                $payload['sort'][] = [$order['column'] => $order['direction']];
            }
        }

        return $this->buildTypePayload(
            $builder->model,
            $payload
        );
    }

    public function update($models)
    {
        $models->map(function ($model) {
            if ($this->updateMapping) {
                Artisan::call(
                    'elastic:update-mapping',
                    ['model' => get_class($model)]
                );
            }

            $documentPayload = $this->buildDocumentPayload($model);
            $searchableFields = $model->toSearchableArray();

            if (ElasticClient::exists($documentPayload)) {
                $payload = $this->buildDocumentPayload(
                    $model,
                    ['doc' => $searchableFields]
                );

                ElasticClient::update($payload);
            } else {
                $payload = $this->buildDocumentPayload(
                    $model,
                    $searchableFields
                );

                ElasticClient::index($payload);
            }
        });

        $this->updateMapping = false;
    }

    public function delete($models)
    {
        $models->map(function ($model) {
            $payload = $this->buildDocumentPayload($model);

            ElasticClient::delete($payload);
        });
    }

    protected function performSearch(Builder $builder, array $options = []) {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                ElasticClient::getFacadeRoot(),
                $builder->query,
                $options
            );
        }

        $results = null;

        if ($builder instanceof SearchBuilder) {
            $searchRules = $builder->rules ?: $builder->model->getSearchRules();

            foreach ($searchRules as $rule) {
                if (is_callable($rule)) {
                    $queryPayload = call_user_func($rule, $builder);
                } else {
                    $queryPayload = (new $rule($builder))->buildQueryPayload();
                }

                $payload = $this->buildSearchQueryPayload(
                    $builder,
                    $queryPayload,
                    $options
                );

                $results = ElasticClient::search($payload);

                if ($this->getTotalCount($results) > 0) {
                    return $results;
                }
            }
        } else {
            $payload = $this->buildSearchQueryPayload(
                $builder,
                ['must' => ['match_all' => []]],
                $options
            );

            $results = ElasticClient::search($payload);
        }

        return $results;
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        return $this->performSearch($builder, [
            'limit' => $perPage,
            'page' => $page
        ]);
    }

    public function searchRaw(SearchableModel $model, $query)
    {
        $payload = $this->buildTypePayload($model, $query);
        return ElasticClient::search($payload);
    }

    public function mapIds($results)
    {
        return array_pluck($results['hits']['hits'], '_id');
    }

    public function map($results, $model)
    {
        if ($this->getTotalCount($results) == 0) {
            return Collection::make();
        }

        $ids = $this->mapIds($results);

        $modelKey = $model->getKeyName();

        $models = $model->whereIn($modelKey, $ids)
                        ->get()
                        ->keyBy($modelKey);

        return Collection::make($results['hits']['hits'])->map(function($hit) use ($models) {
            $id = $hit['_id'];

            if (isset($models[$id])) {
                return $models[$id];
            }
        });
    }

    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }
}