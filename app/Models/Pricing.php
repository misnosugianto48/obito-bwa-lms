<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pricing extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'pricing_id';

    protected $fillable = [
        'name',
        'duration',
        'price'
    ];

    /**
     * Get all of the transactions for the Pricing
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'pricing_id', 'pricing_id');
    }
}
