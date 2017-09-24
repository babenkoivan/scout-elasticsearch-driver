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

    public function get($key = null)
    {
        return array_get($this->payload, $key);
    }
}