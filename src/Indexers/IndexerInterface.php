<?php

namespace ScoutElastic\Indexers;

use Illuminate\Database\Eloquent\Collection;

interface IndexerInterface
{
    public function update(Collection $models);

    public function delete(Collection $models);
}