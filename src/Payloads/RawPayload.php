<?php

namespace ScoutElastic\Payloads;

class RawPayload
{
    protected $payload = [];

    public function set($key, $value)
    {
        if (!is_null($key)) {
            array_set($this->payload, $key, $value);
        }

        return $this;
    }

    public function setIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    public function add($key, $value)
    {
        if (!is_null($key)) {
            $currentValue = array_get($this->payload, $key, []);

            if (!is_array($currentValue)) {
                $currentValue = array_wrap($currentValue);
            }

            $currentValue[] = $value;

            array_set($this->payload, $key, $currentValue);
        }

        return $this;
    }

    public function addIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->add($key, $value);
    }

    public function get($key = null)
    {
        return array_get($this->payload, $key);
    }
}