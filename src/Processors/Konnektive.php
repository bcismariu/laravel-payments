<?php

namespace Bcismariu\Laravel\Payments\Processors;

use Bcismariu\Laravel\Payments\Card;
use Bcismariu\Laravel\Payments\Customer;
use Bcismariu\Laravel\Payments\Product;
use Konnektive\Dispatcher;
use Konnektive\Request\Order\ImportOrderRequest;

class Konnektive
{
    protected $customer;
    protected $credit_card;
    protected $products = [];
    protected $options = [];


    public function __construct($settings = []) {
        unset($settings['driver']);
        $this->options = $settings;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function setCreditCard(Card $card)
    {
        $this->credit_card = $card;
    }

    public function addProduct(Product $product)
    {
        $this->products[] = $product;
    }

    public function setOptions($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function process()
    {
        $request = new ImportOrderRequest();

        $this->applyCustomer($request);
        $this->applyCreditCard($request);
        $this->applyProducts($request);
        $this->applyOptions($request);

        $dispatcher = new Dispatcher();    

        $this->validate($request);

        return $dispatcher->handle($request);
    }

    protected function applyCustomer(ImportOrderRequest &$request)
    {
        $customer = $this->customer;

        $request->firstName     = $customer->first_name;
        $request->lastName      = $customer->last_name;
        $request->companyName   = $customer->company;
        $request->address1      = $customer->address;
        $request->postalCode    = $customer->postcode;
        $request->city          = $customer->city;
        $request->state         = $customer->state;
        $request->country       = $customer->country;
        $request->emailAddress  = $customer->email;
        $request->phoneNumber   = $customer->phone;
        $request->ipAddress     = $customer->ip_address;
    }

    protected function applyCreditCard(ImportOrderRequest &$request)
    {
        $card = $this->credit_card;

        $request->paySource     = 'CREDITCARD';
        $request->cardNumber    = $card->number;
        $request->cardMonth     = $card->exp_month;
        $request->cardYear      = $card->exp_year;
        $request->cardSecurityCode  = $card->cvc_check;
    }

    protected function applyProducts(ImportOrderRequest &$request)
    {
        foreach ($this->products as $index => $product) {
            $this->applyProduct($request, $product, $index + 1);
        }
    }

    protected function applyProduct(ImportOrderRequest $request, $product, $index = 1)
    {
        $prefix = 'product' . $index;

        $request->{$prefix . '_id'}     = $product->id;
        $request->{$prefix . '_qty'}    = $product->quantity;
        $request->{$prefix . '_price'}  = $product->price;
    }

    protected function applyOptions(ImportOrderRequest $request, $options = [])
    {
        foreach ($this->options as $key => $value) {
            $request->$key = $value;
        }
    }

    protected function validate(ImportOrderRequest $request)
    {
        try {
            $request->validate();
        } catch(\Illuminate\Validation\ValidationException $e) {
            dump($request);
            dd($e->validator->errors());
        }
    }
}