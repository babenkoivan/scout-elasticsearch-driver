<?php

namespace App\Stubs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maker extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'title',
    ];

    /**
     * @return HasMany
     */
    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
