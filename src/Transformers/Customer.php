<?php

namespace Bcismariu\Laravel\Payments\Transformers;

use Bcismariu\Laravel\Payments\Customer as CustomerInstance;

class Customer
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function apply()
    {
        return new CustomerInstance([
            'first_name'    => $this->getFirstName(),
            'last_name'     => $this->getLastName(),
            'company'   => '',
            'address'   => $this->model->addr,
            'postcode'  => $this->model->zip,
            'city'      => $this->model->city,
            'state'     => $this->model->state,
            'country'   => $this->model->country,
            'email'     => $this->model->email,
            'phone'     => $this->model->phone,
            'ip_address'    => $this->model->ip,

            'shipAddress1'  => $this->model->addr,
            'shipCity'      => $this->model->city,
            'shipState'     => $this->model->state,
            'shipPostalCode'    => $this->model->zip,
            'shipCountry'       => $this->model->country,
        ]);
    }

    protected function getFirstName()
    {
        $names = explode(' ', trim($this->model->name));
        array_pop($names);
        return implode(' ', $names);
    }

    protected function getLastName()
    {
        $names = explode(' ', trim($this->model->name));
        return array_pop($names);
    }

}