<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;
use ScoutElastic\Facades\ElasticClient;
use ScoutElastic\Migratable;
use ScoutElastic\Payloads\DocumentPayload;

class SingleIndexer implements IndexerInterface
{
    /**
     * {@inheritdoc}
     */
    public function update(Collection $models)
    {
        $models->each(function ($model) {
            if ($model::usesSoftDelete() && config('scout.soft_delete', false)) {
                $model->pushSoftDeleteMetadata();
            }

            $scoutMetaBody = [];
            $scoutMetaOther = [];
            foreach ($model->scoutMetadata() as $k => $v) {
                if (is_string($k) && substr($k, 0, 1) === '_') {
                    $scoutMetaOther[substr($k, 1)] = $v;
                } else {
                    $scoutMetaBody[$k] = $v;
                }
            }

            $modelData = array_merge(
                $model->toSearchableArray(),
                $scoutMetaBody
            );

            if (empty($modelData)) {
                return true;
            }

            $indexConfigurator = $model->getIndexConfigurator();

            $payload = (new DocumentPayload($model))
                ->set('body', $modelData);
            foreach ($scoutMetaOther as $k => $v) {
                $payload->set($k, $v);
            }

            if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
                $payload->useAlias('write');
            }

            if ($documentRefresh = config('scout_elastic.document_refresh')) {
                $payload->set('refresh', $documentRefresh);
            }

            ElasticClient::index($payload->get());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Collection $models)
    {
        $models->each(function ($model) {
            $payload = new DocumentPayload($model);

            if ($documentRefresh = config('scout_elastic.document_refresh')) {
                $payload->set('refresh', $documentRefresh);
            }

            $payload->set('client.ignore', 404);

            ElasticClient::delete($payload->get());
        });
    }
}
