<?php

namespace ScoutElastic\Builders;

use Laravel\Scout\Builder;

class FilterBuilder extends Builder
{
    public function __construct($model, $callback = null)
    {
        $this->model = $model;
        $this->callback = $callback;
    }

    /**
     * Supported operators are =, &gt;, &lt;, &gt;=, &lt;=, &lt;&gt;
     * @param string $field Field name
     * @param mixed $value Scalar value or an array
     * @return $this
     */
    public function where($field, $value)
    {
        $args = func_get_args();

        if (count($args) == 3) {
            list($field, $operator, $value) = $args;
        } else {
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'field' => $field,
            'operator' => $operator,
            'value' => $value
        ];

        return $this;
    }

    public function whereIn($field, array $value)
    {
        $this->wheres[] = [
            'type' => 'in',
            'field' => $field,
            'not' => false,
            'value' => $value
        ];

        return $this;
    }

    public function whereNotIn($field, array $value)
    {
        $this->wheres[] = [
            'type' => 'in',
            'field' => $field,
            'not' => true,
            'value' => $value
        ];

        return $this;
    }

    public function whereBetween($field, array $value)
    {
        $this->wheres[] = [
            'type' => 'between',
            'field' => $field,
            'not' => false,
            'value' => $value
        ];

        return $this;
    }

    public function whereNotBetween($field, array $value)
    {
        $this->wheres[] = [
            'type' => 'between',
            'field' => $field,
            'not' => true,
            'value' => $value
        ];

        return $this;
    }

    public function whereExists($field)
    {
        $this->wheres[] = [
            'type' => 'exists',
            'field' => $field,
            'not' => false
        ];

        return $this;
    }

    public function whereNotExists($field)
    {
        $this->wheres[] = [
            'type' => 'exists',
            'field' => $field,
            'not' => true
        ];

        return $this;
    }

    public function whereRegexp($field, $value, $flags = 'ALL')
    {
        $this->wheres[] = [
            'type' => 'regexp',
            'field' => $field,
            'value' => $value,
            'flags' => $flags
        ];

        return $this;
    }

    public function explain()
    {
        return $this->engine()->explain($this);
    }

    public function profile()
    {
        return $this->engine()->profile($this);
    }
}