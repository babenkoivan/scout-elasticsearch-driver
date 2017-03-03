<?php

namespace ScoutElastic;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

abstract class SearchableModel extends Model
{
    use Searchable;

    protected $indexConfigurator;

    protected $indexType;

    protected $mapping = [];

    /**
     * @return IndexConfigurator
     */
    public function getIndexConfigurator()
    {
        static $configurator;

        if (!$configurator) {
            $configuratorClass = $this->indexConfigurator;
            $configurator = new $configuratorClass;
        }

        return $configurator;
    }

    public function getIndexType()
    {
        return $this->indexType ?: $this->getTable();
    }

    public function getMapping()
    {
        return $this->mapping;
    }
}