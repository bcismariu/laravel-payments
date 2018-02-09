<?php

namespace Bcismariu\Laravel\Payments;

class Customer extends Base\DynamicProperties
{
    protected $attributes = [
        'first_name'    => null,
        'last_name'     => null,
        'company'   => null,
        'address'   => null,
        'postcode'  => null,
        'city'      => null,
        'state'     => null,
        'country'   => null,
        'email'     => null,
        'phone'     => null,
        'ip_address'    => null,
        'billShipSame'  => null,
        'shipAddress1'  => null,
        'shipCity'      => null,
        'shipState'     => null,
        'shipPostalCode'    => null,
        'shipCountry'       => null,
    ];
}