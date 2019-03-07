<?php

namespace ScoutElastic;

use Exception;
use Illuminate\Support\Arr;
use ScoutElastic\Builders\FilterBuilder;
use ScoutElastic\Builders\SearchBuilder;
use Laravel\Scout\Searchable as ScoutSearchable;

trait Searchable
{
    use ScoutSearchable {
        ScoutSearchable::bootSearchable as bootScoutSearchable;
    }

    /**
     * The highligths.
     *
     * @var \ScoutElastic\Highlight|null
     */
    private $highlight = null;

    /**
     * Defines if te model is searchable.
     *
     * @var bool
     */
    private static $isSearchableTraitBooted = false;

    public static function bootSearchable()
    {
        if (self::$isSearchableTraitBooted) {
            return;
        }

        self::bootScoutSearchable();

        self::$isSearchableTraitBooted = true;
    }

    /**
     * Get the index configurator.
     *
     * @return \ScoutElastic\IndexConfigurator
     * @throws \Exception
     */
    public function getIndexConfigurator()
    {
        static $indexConfigurator;

        if (! $indexConfigurator) {
            if (! isset($this->indexConfigurator) || empty($this->indexConfigurator)) {
                throw new Exception(sprintf(
                    'An index configurator for the %s model is not specified.',
                    __CLASS__
                ));
            }

            $indexConfiguratorClass = $this->indexConfigurator;
            $indexConfigurator = new $indexConfiguratorClass;
        }

        return $indexConfigurator;
    }

    /**
     * Get the mapping.
     *
     * @return array
     */
    public function getMapping()
    {
        $mapping = $this->mapping ?? [];

        if ($this::usesSoftDelete() && config('scout.soft_delete', false)) {
            Arr::set($mapping, 'properties.__soft_deleted', ['type' => 'integer']);
        }

        return $mapping;
    }

    /**
     * Get the search rules.
     *
     * @return array
     */
    public function getSearchRules()
    {
        return isset($this->searchRules) && count($this->searchRules) > 0 ?
            $this->searchRules : [SearchRule::class];
    }

    /**
     * Execute the search.
     *
     * @param string $query
     * @param callable|null $callback
     * @return \ScoutElastic\Builders\FilterBuilder|\ScoutElastic\Builders\SearchBuilder
     */
    public static function search($query, $callback = null)
    {
        $softDelete = static::usesSoftDelete() && config('scout.soft_delete', false);

        if ($query == '*') {
            return new FilterBuilder(new static, $callback, $softDelete);
        } else {
            return new SearchBuilder(new static, $query, $callback, $softDelete);
        }
    }

    /**
     * Execute a raw search.
     *
     * @param array $query
     * @return array
     */
    public static function searchRaw(array $query)
    {
        $model = new static();

        return $model->searchableUsing()
            ->searchRaw($model, $query);
    }

    /**
     * Set the highlight attribute.
     *
     * @param \ScoutElastic\Highlight $value
     * @return void
     */
    public function setHighlightAttribute(Highlight $value)
    {
        $this->highlight = $value;
    }

    /**
     * Get the highlight attribute.
     *
     * @return \ScoutElastic\Highlight|null
     */
    public function getHighlightAttribute()
    {
        return $this->highlight;
    }
}
