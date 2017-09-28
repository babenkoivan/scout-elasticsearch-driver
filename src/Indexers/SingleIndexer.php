<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\DocumentPayload;

class SingleIndexer implements IndexerInterface
{
    public function update(Collection $models)
    {
        $models->each(function ($model) {
            $modelData = $model->toSearchableArray();

            if (empty($modelData)) {
                return true;
            }

            $indexConfigurator = $model->getIndexConfigurator();

            $payload = (new DocumentPayload($model))
                ->set('body', $modelData);

            if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
                $payload->useAlias('write');
            }

            ElasticClient::index($payload->get());
        });
    }

    public function delete(Collection $models)
    {
        $models->each(function ($model) {
            $payload = (new DocumentPayload($model))
                ->get();

            ElasticClient::delete($payload);
        });
    }
}