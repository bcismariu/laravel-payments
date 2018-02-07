<?php

namespace Bcismariu\Laravel\Payments;

class Product extends Base\DynamicProperties
{
    protected $attributes = [
        'id'        => null,
        'quantity'  => null,
        'price'     => null,
    ];
}