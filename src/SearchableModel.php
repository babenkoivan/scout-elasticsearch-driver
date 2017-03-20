<?php

namespace ScoutElastic;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;

abstract class SearchableModel extends Model
{
    use Searchable;

    protected $indexConfigurator;

    protected $mapping = [];

    protected $searchRules = [
        SearchRule::class
    ];

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

    public function getSearchRules()
    {
        return $this->searchRules;
    }

    public static function search($query, $callback = null)
    {
        if ($query == '*') {
            return new FilterBuilder(new static, $callback);
        } else {
            return new SearchBuilder(new static, $query, $callback);
        }
    }

    public static function searchRaw($query)
    {
        $model = new static();

        return $model
            ->searchableUsing()
            ->searchRaw($model, $query);
    }
}