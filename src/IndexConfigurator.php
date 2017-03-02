<?php

namespace ScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    protected $name;

    protected $settings = [];

    protected $mappings = [];

    public function getName()
    {
        if (isset($this->name)) {
            return $this->name;
        }

        return Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function toArray()
    {
        $array = ['index' => $this->getName()];

        if ($settings = $this->getSettings()) {
            $array['settings'] = $settings;
        }

        if ($mappings = $this->getMappings()) {
            $array['mappings'] = $mappings;
        }

        return $array;
    }
}