<?php

namespace Bcismariu\Laravel\Payments;

class Card extends Base\DynamicProperties
{
    protected $attributes = [
        'brand'     => null,
        'number'    => null,
        'exp_month' => null,
        'exp_year'  => null,
        'cvc_check' => null,
    ];
}