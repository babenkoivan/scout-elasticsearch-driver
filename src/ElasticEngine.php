<?php

namespace ScoutElastic;

use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Indexers\IndexerInterface;
use ScoutElastic\Payloads\TypePayload;
use stdClass;

class ElasticEngine extends Engine
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * @var bool
     */
    protected $updateMapping;

    /**
     * @var array
     */
    static protected $updatedMappings = [];

    /**
     * @param IndexerInterface $indexer
     * @param $updateMapping
     */
    public function __construct(IndexerInterface $indexer, $updateMapping)
    {
        $this->indexer = $indexer;

        $this->updateMapping = $updateMapping;
    }

    /**
     * @inheritdoc
     */
    public function update($models)
    {
        if ($this->updateMapping) {
            $self = $this;

            $models->each(function ($model) use ($self) {
                $modelClass = get_class($model);

                if (in_array($modelClass, $self::$updatedMappings)) {
                    return true;
                }

                Artisan::call(
                    'elastic:update-mapping',
                    ['model' => $modelClass]
                );

                $self::$updatedMappings[] = $modelClass;
            });
        }

        $this
            ->indexer
            ->update($models);
    }

    /**
     * @inheritdoc
     */
    public function delete($models)
    {
        $this->indexer->delete($models);
    }

    /**
     * @param Builder $builder
     * @param array $options
     * @return array
     */
    public function buildSearchQueryPayloadCollection(Builder $builder, array $options = [])
    {
        $payloadCollection = collect();

        if ($builder instanceof SearchBuilder) {
            $searchRules = $builder->rules ?: $builder->model->getSearchRules();

            foreach ($searchRules as $rule) {
                $payload = new TypePayload($builder->model);

                if (is_callable($rule)) {
                    $payload->setIfNotEmpty('body.query.bool', call_user_func($rule, $builder));
                } else {
                    /** @var SearchRule $ruleEntity */
                    $ruleEntity = new $rule($builder);

                    if ($ruleEntity->isApplicable()) {
                        $payload->setIfNotEmpty('body.query.bool', $ruleEntity->buildQueryPayload());

                        if ($options['highlight'] ?? true) {
                            $payload->setIfNotEmpty('body.highlight', $ruleEntity->buildHighlightPayload());
                        }
                    } else {
                        continue;
                    }
                }

                $payloadCollection->push($payload);
            }
        } else {
            $payload = (new TypePayload($builder->model))
                ->setIfNotEmpty('body.query.bool.must.match_all', new stdClass());

            $payloadCollection->push($payload);
        }

        return $payloadCollection->map(function (TypePayload $payload) use ($builder, $options) {
            $payload
                ->setIfNotEmpty('body._source', $builder->select)
                ->setIfNotEmpty('body.collapse.field', $builder->collapse)
                ->setIfNotEmpty('body.sort', $builder->orders)
                ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
                ->setIfNotEmpty('body.profile', $options['profile'] ?? null)
                ->setIfNotNull('body.from', $builder->offset)
                ->setIfNotNull('body.size', $builder->limit);


            foreach ($builder->wheres as $clause => $filters) {
                $clauseKey = 'body.query.bool.filter.bool.' . $clause;

                $clauseValue = array_merge(
                    $payload->get($clauseKey, []),
                    $filters
                );

                $payload->setIfNotEmpty($clauseKey, $clauseValue);
            }

            return $payload->get();
        });
    }

    /**
     * @param Builder $builder
     * @param array $options
     * @return array
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        if ($builder->callback) {
            return call_user_func(
                $builder->callback,
                ElasticClient::getFacadeRoot(),
                $builder->query,
                $options
            );
        }

        $results = [];

        $this
            ->buildSearchQueryPayloadCollection($builder, $options)
            ->each(function ($payload) use (&$results) {
                $results = ElasticClient::search($payload);

                $results['_payload'] = $payload;

                if ($this->getTotalCount($results) > 0) {
                    return false;
                }
            });

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    /**
     * @inheritdoc
     */
    public function paginate(Builder $builder, $perPage, $page)
    {
        $builder
            ->from(($page - 1) * $perPage)
            ->take($perPage);

        return $this->performSearch($builder);
    }

    /**
     * @param Builder $builder
     * @return array
     */
    public function explain(Builder $builder)
    {
        return $this->performSearch($builder, [
            'explain' => true
        ]);
    }

    /**
     * @param Builder $builder
     * @return array
     */
    public function profile(Builder $builder)
    {
        return $this->performSearch($builder, [
            'profile' => true
        ]);
    }

    /**
     * @param Builder $builder
     * @return int
     */
    public function count(Builder $builder)
    {
        $count = 0;

        $this
            ->buildSearchQueryPayloadCollection($builder, ['highlight' => false])
            ->each(function ($payload) use (&$count) {
                $result = ElasticClient::count($payload);

                $count = $result['count'];

                if ($count > 0) {
                    return false;
                }
            });

        return $count;
    }

    /**
     * @param Model $model
     * @param array $query
     * @return array
     */
    public function searchRaw(Model $model, $query)
    {
        $payload = (new TypePayload($model))
            ->setIfNotEmpty('body', $query)
            ->get();

        return ElasticClient::search($payload);
    }

    /**
     * @inheritdoc
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])->pluck('_id');
    }

    /**
     * @inheritdoc
     */
    public function map(Builder $builder, $results, $model)
    {
        if ($this->getTotalCount($results) == 0) {
            return Collection::make();
        }

        $primaryKey = $model->getKeyName();

        $columns = array_get($results, '_payload.body._source');

        if (is_null($columns)) {
            $columns = ['*'];
        } else {
            $columns[] = $primaryKey;
        }

        $ids = $this->mapIds($results)->all();

        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $models = $query
            ->whereIn($primaryKey, $ids)
            ->get($columns)
            ->keyBy($primaryKey);

        return Collection::make($results['hits']['hits'])
            ->map(function ($hit) use ($models) {
                $id = $hit['_id'];

                if (isset($models[$id])) {
                    $model = $models[$id];

                    if (isset($hit['highlight'])) {
                        $model->highlight = new Highlight($hit['highlight']);
                    }

                    return $model;
                }
            })
            ->filter()
            ->values();
    }

    /**
     * @inheritdoc
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }

    /**
     * @inheritdoc
     */
    public function flush($model)
    {
        $query = $model::usesSoftDelete() ? $model->withTrashed() : $model->newQuery();

        $query
            ->orderBy($model->getKeyName())
            ->unsearchable();
    }
}
