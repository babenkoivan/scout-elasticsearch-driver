<?php

namespace ScoutElastic;

use Artisan;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use ScoutElastic\Builders\SearchBuilder;
use ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Payloads\DocumentPayload;
use ScoutElastic\Payloads\TypePayload;
use stdClass;

class ElasticEngine extends Engine
{
    protected $updateMapping = false;

    protected $query;

    protected $result;

    public function __construct()
    {
        $this->updateMapping = config('scout_elastic.update_mapping');
    }

    public function update($models)
    {
        $models->each(function ($model) {
            if ($this->updateMapping) {
                Artisan::call(
                    'elastic:update-mapping',
                    ['model' => get_class($model)]
                );
            }

            $array = $model->toSearchableArray();

            if (empty($array)) {
                return true;
            }

            $payload = (new DocumentPayload($model))
                ->set('body', $array)
                ->get();

            ElasticClient::index($payload);
        });

        $this->updateMapping = false;
    }

    public function delete($models)
    {
        $models->each(function ($model) {
            $payload = (new DocumentPayload($model))
                ->get();

            ElasticClient::delete($payload);
        });
    }

    protected function buildSearchQueryPayload(Builder $builder, $queryPayload, array $options = [])
    {
        foreach ($builder->wheres as $clause => $filters) {
            if (count($filters) == 0) {
                continue;
            }

            if (!array_has($queryPayload, 'filter.bool.'.$clause)) {
                array_set($queryPayload, 'filter.bool.'.$clause, []);
            }

            $queryPayload['filter']['bool'][$clause] = array_merge(
                $queryPayload['filter']['bool'][$clause],
                $filters
            );
        }

        $payload = (new TypePayload($builder->model))
            ->setIfNotEmpty('body.query.bool', $queryPayload)
            ->setIfNotEmpty('body.sort', $builder->orders)
            ->setIfNotEmpty('body.explain', $options['explain'] ?? null)
            ->setIfNotEmpty('body.profile', $options['profile'] ?? null);

        if ($size = isset($options['limit']) ? $options['limit'] : $builder->limit) {
            $payload->set('body.size', $size);

            if (isset($options['page'])) {
                $payload->set('body.from', ($options['page'] - 1) * $size);
            }
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
            $this->query = $builder->query;
            return $this->result = call_user_func(
                $builder->callback,
                ElasticClient::getFacadeRoot(),
                $builder->query,
                $options
            );
        }

        $result = null;

        $this->buildSearchQueryPayloadCollection($builder, $options)->each(function($payload) use (&$result) {
            $result = ElasticClient::search($payload);

            $this->query  = array_get($payload, 'body.query');
            $this->result = $result;

            if ($this->getTotalCount($result) > 0) {
                return false;
            }
        });

        $this->result = $result;

        return $result;
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

        return Collection::make($results['hits']['hits'])->map(function($hit) use ($models) {
            $id = $hit['_id'];

            if (isset($models[$id])) {
                return $models[$id];
            }
        })->filter();
    }

    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
