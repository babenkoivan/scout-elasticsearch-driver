<?php

namespace ScoutElastic\Payloads;

use Illuminate\Support\Arr;

class RawPayload
{
    /**
     * The payload.
     *
     * @var array
     */
    protected $payload = [];

    /**
     * Set a value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value)
    {
        if (! is_null($key)) {
            Arr::set($this->payload, $key, $value);
        }

        return $this;
    }

    /**
     * Set a value if it's not empty.
     *
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
     * Set a value if it's not null.
     *
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
     * Checks that the payload key has a value.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->payload, $key);
    }

    /**
     * Add a value.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add($key, $value)
    {
        if (! is_null($key)) {
            $currentValue = Arr::get($this->payload, $key, []);

            if (! is_array($currentValue)) {
                $currentValue = Arr::wrap($currentValue);
            }

            $currentValue[] = $value;

            Arr::set($this->payload, $key, $currentValue);
        }

        return $this;
    }

    /**
     * Add a value if it's not empty.
     *
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
     * Get value.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        return Arr::get($this->payload, $key, $default);
    }
}
