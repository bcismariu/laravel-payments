<?php

namespace Bcismariu\Laravel\Payments;

class Product extends Base\DynamicProperties
{
    protected $attributes = [
        'id'        => 1,
        'quantity'  => 1,
        'price'     => 0,
    ];
}