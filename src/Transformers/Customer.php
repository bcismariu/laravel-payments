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
            'first_name'    => $this->model->name,
            'last_name'     => $this->model->name,
            'company'   => '',
            'address'   => $this->model->addr,
            'postcode'  => $this->model->zip,
            'city'      => $this->model->city,
            'state'     => $this->model->state,
            'country'   => $this->model->country,
            'email'     => $this->model->email,
            'phone'     => $this->model->phone,
            'ip_address'    => $this->model->ip,
        ]);
    }

}