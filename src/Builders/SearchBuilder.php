<?php

namespace ScoutElastic\Builders;

use Illuminate\Database\Eloquent\Model;

class SearchBuilder extends FilterBuilder
{
    /**
     * @var array
     */
    public $rules = [];

    /**
     * @param Model $model
     * @param string $query
     * @param callable|null $callback
     * @param bool $softDelete
     */
    public function __construct(Model $model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $callback, $softDelete);

        $this->query = $query;
    }

    /**
     * @param string|callable $rule Search rule class name or function
     * @return $this
     */
    public function rule($rule)
    {
        $this->rules[] = $rule;

        return $this;
    }
}
