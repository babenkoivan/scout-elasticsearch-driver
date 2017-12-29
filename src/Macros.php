<?php

use Laravel\Scout\Builder;
use Illuminate\Database\Eloquent\Collection;


if (! Builder::hasMacro('count')) {
    /**
     * Return the total amount of results for the current query.
     *
     * @return int Number of results
     */
    Builder::macro('count', function () {
        $results = $this->engine()->search($this);

        return (int) $results['hits']['total'];
    });
}

if (! Builder::hasMacro('hydrate')) {
    /**
     * get() hydrates records by looking up the Ids in the corresponding database
     * This macro uses the data returned from the search results to hydrate
     *  the models and return a collection
     *
     * @return Collection
     */
    Builder::macro('hydrate', function () {
        $results = $this->engine()->search($this);

        if ($results['hits']['total'] === 0) {
            return Collection::make();
        }

        $hits = collect($results['hits']['hits']);
        $className = get_class($this->model);
        $models = new Collection();

        /* If the model is fully guarded, we unguard it.
        Fully garded is the default configuration and it will
        only result in error.
        If the `$guarded` attribute is set to a list of attribute
        we take it into account. */
        if (in_array('*', $this->model->getGuarded())) {
            Eloquent::unguard();
        }

        $hits->each(function($item, $key) use ($className, $models) {
            $models->push(new $className($item));
        });

        Eloquent::reguard();

        return $models;
    });
}
