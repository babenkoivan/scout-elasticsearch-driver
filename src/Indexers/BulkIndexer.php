<?php

namespace ScoutElastic\Indexers;

use ScoutElastic\Migratable;
use ScoutElastic\Payloads\RawPayload;
use ScoutElastic\Payloads\TypePayload;
use ScoutElastic\Facades\ElasticClient;
use Illuminate\Database\Eloquent\Collection;

class BulkIndexer implements IndexerInterface
{
    /**
     * @param Collection $models
     *
     * @return array|void
     * @throws \Exception
     */
    public function update(Collection $models)
    {
        $model = $models->first();
        $indexConfigurator = $model->getIndexConfigurator();

        $bulkPayload = new TypePayload($model);

        if (in_array(Migratable::class, class_uses_recursive($indexConfigurator))) {
            $bulkPayload->useAlias('write');
        }

        if ($documentRefresh = config('scout_elastic.document_refresh')) {
            $bulkPayload->set('refresh', $documentRefresh);
        }

        $models->each(function ($model) use ($bulkPayload) {
            if ($model::usesSoftDelete() && config('scout.soft_delete', false)) {
                $model->pushSoftDeleteMetadata();
            }

            $modelData = array_merge(
                $model->toSearchableArray(),
                $model->scoutMetadata()
            );

            if (empty($modelData)) {
                return true;
            }

            $actionPayload = (new RawPayload())
                ->set('index._id', $model->getKey());

            $bulkPayload
                ->add('body', $actionPayload->get())
                ->add('body', $modelData);
        });

        $response = ElasticClient::bulk($bulkPayload->get());

        if ($response['errors']) {

            // Response included every record's status which is a lot to dig through when chunking by thousand
            // Sort through the items to only log the failed items
            foreach ($response['items'] as $item) {
                if ($item['index']['error']) {
                    Log::error($item);
                }
            }

            throw new Exception('ElasticSearch responded with an error');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Collection $models)
    {
        $model = $models->first();

        $bulkPayload = new TypePayload($model);

        $models->each(function ($model) use ($bulkPayload) {
            $actionPayload = (new RawPayload())
                ->set('delete._id', $model->getKey());

            $bulkPayload->add('body', $actionPayload->get());
        });

        $bulkPayload->set('client.ignore', 404);

        ElasticClient::bulk($bulkPayload->get());
    }
}
