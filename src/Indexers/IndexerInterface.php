<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
    /**
     * Update documents.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return array
     */
    public function update(Collection $models);

    /**
     * Delete documents.
     *
     * @param \Illuminate\Database\Eloquent\Collection $models
     * @return array
     */
    public function delete(Collection $models);
}
