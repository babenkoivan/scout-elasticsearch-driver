<?php

namespace ScoutElastic\Payloads;

use Exception;
use Illuminate\Database\Eloquent\Model;

class DocumentPayload extends TypePayload
{
    /**
     * DocumentPayload constructor.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @throws \Exception
     * @return void
     */
    public function __construct(Model $model)
    {
        if ($model->getScoutKey() === null) {
            throw new Exception(sprintf(
                'The key value must be set to construct a payload for the %s instance.',
                get_class($model)
            ));
        }

        parent::__construct($model);

        $this->payload['id'] = $model->getScoutKey();
        $this->protectedKeys[] = 'id';
    }
}
