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
        static $indexConfigurator;

        if (!$indexConfigurator) {
            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
        }

        return $indexConfigurator;
    }

    public function getMapping()
    {
        return $this->mapping;
    }
}