<?php
/**
 * Created by PhpStorm.
 * User: artiom
 * Date: 09.09.17
 * Time: 10:54
 */

namespace ScoutElastic\Console\Features;

/**
 * Trait CompatibilityTrait
 *
 * @package ScoutElastic\Console\Features
 */
trait CompatibilityTrait
{
    /**
     * Method for laravel >=5.5
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->handleCommand();
    }

    /**
     * Method for laravel <=5.4
     *
     * @return mixed
     */
    public function fire()
    {
        return $this->handleCommand();
    }

    /**
     * Should be used instead direct call of parent::handle()|parent::fire() in commands, that extends existing ones
     *
     * @return mixed|void
     */
    public function callParentHandler() {
        // >=5.5
        if(method_exists(get_parent_class($this), 'handle')) {
            return parent::handle();
        }

        // <=5.4
        if(method_exists(get_parent_class($this), 'fire')) {
            return parent::fire();
        }

        return;
    }
}