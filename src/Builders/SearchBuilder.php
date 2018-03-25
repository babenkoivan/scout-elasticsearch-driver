<?php

namespace ScoutElastic\Builders;

use Illuminate\Database\Eloquent\Model;

class SearchBuilder extends FilterBuilder
{
    public $rules = [];

    /**
     * @param Model $model
     * @param string $query
     * @param callable|null $callback
     * @param bool $softDelete
     */
    public function __construct($model, $query, $callback = null, $softDelete = false)
    {
        parent::__construct($model, $callback, $softDelete);

        $this->query = $query;
    }

    public function rule($rule)
    {
        $this->rules[] = $rule;

        return $this;
    }
}
