<?php

namespace ScoutElastic;

use Config;
use Artisan;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use ScoutElastic\Facades\ElasticClient;

class ElasticEngine extends Engine
{
    protected $updateMapping = false;

    public function __construct()
    {
        $this->updateMapping = Config::get('scout_elastic.update_mapping');
    }

    /**
     * @param SearchableModel $model
     * @return array
     */
    protected function buildBasePayload($model)
    {
        $configurator = $model->getIndexConfigurator();

        return [
            'index' => $configurator->getName(),
            'type' => $model->searchableAs(),
            'id' => $model->getKey(),
        ];
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

            $basePayload = $this->buildBasePayload($model);
            $searchableFields = $model->toSearchableArray();

            if (ElasticClient::exists($basePayload)) {
                ElasticClient::update(array_merge(
                    $basePayload,
                    [
                        'body' => [
                            'doc' => $searchableFields
                        ]
                    ]
                ));
            } else {
                ElasticClient::index(array_merge(
                    $basePayload,
                    [
                        'body' => $searchableFields
                    ]
                ));
            }
        });

        $this->updateMapping = false;
    }

    public function delete($models)
    {
        $models->map(function ($model) {
            $basePayload = $this->buildBasePayload($model);

            ElasticClient::delete($basePayload);
        });
    }

    public function search(Builder $builder)
    {

    }

    public function getTotalCount($results)
    {

    }

    public function paginate(Builder $builder, $perPage, $page)
    {

    }

    public function mapIds($results)
    {

    }

    public function map($results, $model)
    {

    }
}