<?php

namespace Bcismariu\Laravel\Payments;

class OrderItem extends Base\DynamicProperties
{
    protected $attributes = [
        'order_id'     => null,
        'customer_id'    => null,
        'date_created' => null,
        'date_updated'  => null,
        'order_type' => null,
        'order_status' => null,
        'product_id' => null,
        'purchase_status' => null,
        'next_bill_date' => null,
    ];

    public function isValid()
    {
        return $this->order_status == 'COMPLETE'
            && $this->purchase_status == 'ACTIVE'
        ;
    }
}