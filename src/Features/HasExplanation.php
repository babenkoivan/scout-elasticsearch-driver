<?php

namespace ScoutElastic\Features;

trait HasExplanation
{
    protected $explanation;

    public function getExplanation()
    {
        return $this->explanation;
    }

    public function setExplanation(array $explanation)
    {
        $this->explanation = $explanation;
    }
}