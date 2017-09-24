<?php

namespace ScoutElastic;

trait Migratable
{
    public function getWriteAlias()
    {
        return $this->getName().'_write';
    }
}