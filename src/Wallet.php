<?php

namespace Depsimon\Wallet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Wallet extends Model
{
    use SoftDeletes;

    /**
     * Retrieve all transactions
     */
    public function transactions()
    {
        return $this->hasMany(config('wallet.transaction_model', Transaction::class));
    }

    /**
     * Retrieve owner
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Set the wallet's address.
     *
     * @param  string  $value
     * @return void
     */
    public function setAddressAttribute($value)
    {
        return $value? $value : substr((string) Str::uuid(), 4, 19);
    }
}