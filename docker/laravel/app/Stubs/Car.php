<?php

namespace App\Stubs;

use ScoutElastic\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Car extends Model
{
    use Searchable;

    /**
     * @var string
     */
    protected $indexConfigurator = CarIndexConfigurator::class;

    /**
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    /**
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'title' => [
                'type' => 'text',
            ],
            'maker' => [
                'type' => 'keyword',
            ],
            'created_at' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ],
            'updated_at' => [
                'type' => 'date',
                'format' => 'yyyy-MM-dd HH:mm:ss',
            ],
        ],
    ];

    /**
     * @return BelongsTo
     */
    public function maker()
    {
        return $this->belongsTo(Maker::class);
    }

    /**
     * {@inheritdoc}
     */
    public function toSearchableArray()
    {
        $searchableArray = $this->toArray();

        unset($searchableArray['maker_id']);

        $searchableArray['maker'] = $this
            ->maker
            ->title;

        return $searchableArray;
    }
}
