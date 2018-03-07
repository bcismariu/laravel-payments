<?php

namespace Bcismariu\Laravel\Payments;

use Carbon\Carbon;

class Subscription extends ParentModel
{
    protected $table = 'payments_subscriptions';

    protected $fillable = [
        'plan',
        'product_id',
        'customer_id',
        'order_id',
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

    public function isActive()
    {
        return $this->status == 'active'
            && $this->ends_at->gt(Carbon::now())
        ;
    }

    public function scopeActive($query)
    {
        return $query->whereStatus('active')
            ->where('ends_at', '>', Carbon::now())
        ;
    }
}