<?php

namespace Bcismariu\Laravel\Payments\Processors;

use Bcismariu\Laravel\Payments\Card;
use Bcismariu\Laravel\Payments\Customer;
use Bcismariu\Laravel\Payments\Product;
use Konnektive\Dispatcher;
use Konnektive\Request\Order\ImportOrderRequest;
use Konnektive\Response\Response as KonnektiveResponse;

class Konnektive
{
    protected $customer;
    protected $credit_card;
    protected $products = [];
    protected $options = [];
    protected $request;


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
        $this->request = new ImportOrderRequest();

        $this->applyCustomer();
        $this->applyCreditCard();
        $this->applyProducts();
        $this->applyOptions();

        $this->validate();

        $dispatcher = new Dispatcher();    
        $response = $dispatcher->handle($this->request);
        return $this->transformResponse($response);
    }

    protected function applyCustomer()
    {
        $customer = $this->customer;

        $this->request->firstName     = $customer->first_name;
        $this->request->lastName      = $customer->last_name;
        $this->request->companyName   = $customer->company;
        $this->request->address1      = $customer->address;
        $this->request->postalCode    = $customer->postcode;
        $this->request->city          = $customer->city;
        $this->request->state         = $customer->state;
        $this->request->country       = $customer->country;
        $this->request->emailAddress  = $customer->email;
        $this->request->phoneNumber   = $customer->phone;
        $this->request->ipAddress     = $customer->ip_address;

        $this->request->shipAddress1  = $customer->shipAddress1;
        $this->request->shipCity      = $customer->shipCity;
        $this->request->shipState     = $customer->shipState;
        $this->request->shipPostalCode    = $customer->shipPostalCode;
        $this->request->shipCountry       = $customer->shipCountry;
    }

    protected function applyCreditCard()
    {
        $card = $this->credit_card;

        $this->request->paySource     = 'CREDITCARD';
        $this->request->cardNumber    = $card->number;
        $this->request->cardMonth     = $card->exp_month;
        $this->request->cardYear      = $card->exp_year;
        $this->request->cardSecurityCode  = $card->cvc_check;
    }

    protected function applyProducts()
    {
        foreach ($this->products as $index => $product) {
            $this->applyProduct($product, $index + 1);
        }
    }

    protected function applyProduct($product, $index = 1)
    {
        $prefix = 'product' . $index;

        $this->request->{$prefix . '_id'}     = $product->id;
        $this->request->{$prefix . '_qty'}    = $product->quantity;
        $this->request->{$prefix . '_price'}  = $product->price;
    }

    protected function applyOptions($options = [])
    {
        foreach ($this->options as $key => $value) {
            $this->request->$key = $value;
        }
    }

    protected function validate()
    {
        try {
            $this->request->validate();
        } catch(\Illuminate\Validation\ValidationException $e) {
            // dump($this->request);
            // dd($e->validator->errors());
            throw $e;
        }
    }

    protected function transformResponse(KonnektiveResponse $konnektive)
    {
        $response = new Response();
        $response->raw = $konnektive->raw;
        $response->message = $konnektive->message;
        $response->status = strtolower(trim($konnektive->result));
        return $response;
    }
}