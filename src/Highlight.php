<?php

namespace ScoutElastic;

class Highlight implements \ArrayAccess, \Iterator
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

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->highlight[] = $value;
        } else {
            $this->highlight[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->highlight[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->highlight[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->highlight[$offset]) ? $this->highlight[$offset] : null;
    }

    public function current()
    {
        return current($this->highlight);
    }

    public function next()
    {
        return next($this->highlight);
    }

    public function key()
    {
        return key($this->highlight);
    }

    public function valid()
    {
        $key = key($this->highlight);

        return ($key !== null && $key !== false);
    }

    public function rewind()
    {
        reset($this->highlight);
    }


}
