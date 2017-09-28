<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\RawPayload;

class BulkIndexer implements IndexerInterface
{
    public function update(Collection $models)
    {
        $bulkPayload = new RawPayload();

        $models->each(function ($model) use ($bulkPayload) {
            $modelData = $model->toSearchableArray();

            if (empty($modelData)) {
                return true;
            }

            $indexConfigurator = $model->getIndexConfigurator();

            $actionPayload = (new RawPayload())
                ->set('index._type', $model->searchableAs())
                ->set('index._id', $model->getKey());

            if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
                $actionPayload->set('index._index', $indexConfigurator->getWriteAlias());
            } else {
                $actionPayload->set('index._index', $indexConfigurator->getName());
            }

            $bulkPayload->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        ElasticClient::bulk($bulkPayload->get());
    }

    public function delete(Collection $models)
    {
        $bulkPayload = new RawPayload();

        $models->each(function ($model) use ($bulkPayload) {
            $indexConfigurator = $model->getIndexConfigurator();

            $actionPayload = (new RawPayload())
                ->set('delete._index', $indexConfigurator->getName())
                ->set('delete._type', $model->searchableAs())
                ->set('delete._id', $model->getKey());

            $bulkPayload->add('body', $actionPayload->get());
        });

        ElasticClient::bulk($bulkPayload->get());
    }
}