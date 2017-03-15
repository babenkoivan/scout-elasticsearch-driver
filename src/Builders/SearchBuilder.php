<?php

namespace ScoutElastic\Builders;

class SearchBuilder extends FilterBuilder {
    public $rules = [];

    public function __construct($model, $query, $callback = null)
    {
        $this->model = $model;
        $this->query = $query;
        $this->callback = $callback;
    }

    public function rule($rule)
    {
        $this->rules[] = $rule;

        return $this;
    }
}