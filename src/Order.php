<?php

namespace Bcismariu\Laravel\Payments;

class Order extends ParentModel
{
    protected $table = 'payments_orders';

    protected $fillable = [
        'customer_id',
        'order_id',
        'campaign_id',
        'status',
        'product_id',
        'amount'
    ];

    public function billable()
    {
        return $this->morphTo();
    }
}