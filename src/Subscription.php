<?php

namespace Bcismariu\Laravel\Payments;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'payments_subscriptions';

    protected $fillable = [
        'plan',
        'status',
        'ends_at',
    ];

    protected $dates = [
        'ends_at',
        'created_at',
        'updated_at',
    ];

    /**
     * accessor method
     */
    public function user()
    {
        return $this->subscriber();
    }

    /**
     * returns the subscription owner
     */
    public function subscriber()
    {
        return $this->morphTo();
    }
}