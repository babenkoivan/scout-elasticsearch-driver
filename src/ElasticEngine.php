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
    protected $indexer;

    protected $updateMapping;

    protected $trackScores;

    static protected $updatedMappings = [];

    public function __construct(IndexerInterface $indexer, $updateMapping, $trackScores)
    {
        $this->indexer = $indexer;

        $this->updateMapping = $updateMapping;

        $this->trackScores = $trackScores;
    }

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

        $this->indexer->update($models);
    }

    public function delete($models)
    {
        $this->indexer->delete($models);
    }

    protected function buildSearchQueryPayload(Builder $builder, $queryPayload, array $options = [])
    {
        foreach ($builder->wheres as $clause => $filters) {
            if (count($filters) == 0) {
                continue;
            }

            if (! array_has($queryPayload, 'filter.bool.'.$clause)) {
                array_set($queryPayload, 'filter.bool.'.$clause, []);
            }

            $queryPayload['filter']['bool'][$clause] = array_merge(
                $queryPayload['filter']['bool'][$clause],
                $filters
            );
        }

        $payload = (new TypePayload($builder->model))
            ->setIfNotEmpty('body.query.bool', $queryPayload)
            ->setIfNotEmpty('body.collapse', $builder->collapse)
            ->setIfNotEmpty('body.sort', $builder->orders)
            ->setIfNotEmpty('body.aggs', $builder->aggregates)
            ->setIfNotEmpty('body.suggest', $builder->suggesters)
            ->setIfNotEmpty('body.highlight', $builder->highlighter)
            ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
            ->setIfNotEmpty('body.profile', $options['profile'] ?? null)
            ->setIfNotEmpty('body.track_scores', $this->trackScores);

        if (($size = isset($options['limit']) ? $options['limit'] : $builder->limit) || $builder->aggregates || $builder->suggesters) {
            $payload->set('body.size', $size);
        }

        if (isset($builder->offset)) {
            $payload->set('body.from', $builder->offset);
        }

        if (isset($builder->limit)) {
            $payload->set('body.size', $builder->limit);
        }

        return $payload->get();
    }

    public function buildSearchQueryPayloadCollection(Builder $builder, array $options = [])
    {
        $payloadCollection = collect();

        if ($builder instanceof SearchBuilder) {
            $searchRules = $builder->rules ?: $builder->model->getSearchRules();

            foreach ($searchRules as $rule) {
                if (is_callable($rule)) {
                    $queryPayload = call_user_func($rule, $builder);
                } else {
                    /** @var SearchRule $ruleEntity */
                    $ruleEntity = new $rule($builder);

                    if ($ruleEntity->isApplicable()) {
                        $queryPayload = $ruleEntity->buildQueryPayload();
                    } else {
                        continue;
                    }
                }

                $payload = $this->buildSearchQueryPayload(
                    $builder,
                    $queryPayload,
                    $options
                );

                $payloadCollection->push($payload);
            }
        } else {
            $payload = $this->buildSearchQueryPayload(
                $builder,
                ['must' => ['match_all' => new stdClass()]],
                $options
            );

            $payloadCollection->push($payload);
        }

        return $payloadCollection;
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

        $result = null;

        $this->buildSearchQueryPayloadCollection($builder, $options)->each(function($payload) use (&$result) {
            $result = ElasticClient::search($payload);

            if ($this->getTotalCount($result) > 0) {
                return false;
            }
        });

        $result['builder'] = $builder;

        return $result;
    }

    public function search(Builder $builder)
    {
        return $this->performSearch($builder);
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        $builder
            ->from(($page - 1) * $perPage)
            ->take($perPage);

        return $this->performSearch($builder);
    }

    public function explain(Builder $builder)
    {
        return $this->performSearch($builder, [
            'explain' => true
        ]);
    }

    public function profile(Builder $builder)
    {
        return $this->performSearch($builder, [
            'profile' => true
        ]);
    }

    public function searchRaw(Model $model, $query)
    {
        $payload = (new TypePayload($model))
            ->setIfNotEmpty('body', $query)
            ->get();

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

        $collection = Collection::make($results['hits']['hits'])->map(function($hit) use ($models) {
            $id = $hit['_id'];

            if (isset($models[$id])) {
                $model = $models[$id];
                $model->_score = $hit['_score'];
                return $model;
            }
        })->filter()->values();

        $builder = $results['builder'];

        if (isset($builder->with) && $collection->count() > 0) {
            $collection->load($builder->with);
        }

        return $collection;
    }

    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }
}
