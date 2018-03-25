<?php

namespace ScoutElastic;

use Illuminate\Support\Str;

abstract class IndexConfigurator
{
    protected $name;

    protected $settings = [];

    protected $defaultMapping = [];

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->name ?? Str::snake(str_replace('IndexConfigurator', '', class_basename($this)));
        return config('scout.prefix') . $name;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @deprecated
     */
    public function getDefaultMapping()
    {
        return $this->defaultMapping;
    }
}