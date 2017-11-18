<?php

namespace Bcismariu\Laravel\Payments\Processors;

use Bcismariu\Laravel\Payments\Base\DynamicProperties;

class Response extends DynamicProperties
{
    public $raw;
    public $message;
    public $status;
    protected $attributes = [
        'customer_id'   => null,
        'order_id'      => null,
        'campaign_id'   => null,
        'status'        => null,
        'product_id'    => null,
        'amount'        => null,
    ];

    public function isSuccessful()
    {
        return $this->status == 'success';
    }
}