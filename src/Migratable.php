<?php

namespace ScoutElastic;

trait Migratable
{
    /**
     * @return string
     */
    public function getWriteAlias()
    {
        return $this->getName().'_write';
    }
}