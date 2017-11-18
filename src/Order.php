<?php

namespace Bcismariu\Laravel\Payments;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
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