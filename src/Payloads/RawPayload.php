<?php

namespace ScoutElastic\Payloads;

class RawPayload
{
    /**
     * @var array
     */
    protected $payload = [];

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (!is_null($key)) {
            array_set($this->payload, $key, $value);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setIfNotNull($key, $value)
    {
        if (is_null($value)) {
            return $this;
        }

        return $this->set($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_has($this->payload, $key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
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

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addIfNotEmpty($key, $value)
    {
        if (empty($value)) {
            return $this;
        }

        return $this->add($key, $value);
    }

    /**
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return array_get($this->payload, $key, $default);
    }
}