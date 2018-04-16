<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
    /**
     * @param Collection $models
     * @return array
     */
    public function update(Collection $models);

    /**
     * @param Collection $models
     * @return array
     */
    public function delete(Collection $models);
}