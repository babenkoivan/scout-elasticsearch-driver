<?php

namespace ScoutElastic;

use Laravel\Scout\Searchable as ScoutSearchable;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use \Exception;

trait Searchable {
    use ScoutSearchable;

    /**
     * @return IndexConfigurator
     * @throws Exception If an index configurator is not specified
     */
    public function getIndexConfigurator()
    {
        static $indexConfigurator;

        if (!$indexConfigurator) {
            if (!isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
                throw new Exception(sprintf('An index configurator for the %s model is not specified.', __CLASS__));
            }

            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
        }

        return $indexConfigurator;
    }

    public function getMapping()
    {
        return isset($this->mapping) ? $this->mapping : [];
    }

    public function getSearchRules()
    {
        return isset($this->searchRules) && count($this->searchRules) > 0 ? $this->searchRules : [SearchRule::class];
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

        return $model->searchableUsing()
            ->searchRaw($model, $query);
    }
}
