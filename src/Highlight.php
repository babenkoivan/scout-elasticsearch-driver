<?php

namespace ScoutElastic;

class Highlight
{
    /**
     * The highlight array.
     *
     * @var array
     */
    private $highlight;

    /**
     * Highlight constructor.
     *
     * @param array $highlight
     * @return void
     */
    public function __construct(array $highlight)
    {
        $this->highlight = $highlight;
    }

    /**
     * Get a value.
     *
     * @param string $key
     * @return mixed|string|null
     */
    public function __get($key)
    {
        $field = str_replace('AsString', '', $key);

        if (isset($this->highlight[$field])) {
            $value = $this->highlight[$field];

            return $field == $key ? $value : implode(' ', $value);
        } else {
            return;
        }
    }
}
