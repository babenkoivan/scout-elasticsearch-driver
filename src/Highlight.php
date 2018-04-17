<?php

namespace ScoutElastic;

class Highlight
{
    /**
     * @var array
     */
    private $highlight;

    /**
     * @param array $highlight
     */
    public function __construct(array $highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * @param $key
     * @return array|string|null
     */
    public function __get($key)
    {
        $field = str_replace('AsString', '', $key);

        if (isset($this->highlight[$field])) {
            $value = $this->highlight[$field];
            return $field == $key ? $value : implode(' ', $value);
        } else {
            return null;
        }
    }
}