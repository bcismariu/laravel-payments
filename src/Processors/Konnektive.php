<?php

namespace Bcismariu\Laravel\Payments\Processors;

use Bcismariu\Laravel\Payments\Card;
use Bcismariu\Laravel\Payments\Customer;
use Bcismariu\Laravel\Payments\Product;
use Bcismariu\Commons\Descendable\Descendable;
use Konnektive\Dispatcher;
use Konnektive\Request\Order\ImportOrderRequest;
use Konnektive\Request\Order\QueryOrderRequest;
use Konnektive\Request\Order\CancelOrderRequest;
use Konnektive\Request\Purchase\QueryPurchasesRequest;
use Konnektive\Response\Response as KonnektiveResponse;
use Illuminate\Support\Collection;

class Konnektive
{
    protected $customer;
    protected $credit_card = null;
    protected $products = [];
    protected $options = [];
    protected $request;


    public function __construct($settings = [])
    {
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

    public function sync()
    {
        $this->request = new QueryOrderRequest();

        $this->applyOptions();
        $this->validate();

        $dispatcher = new Dispatcher();
        $response = $dispatcher->handle($this->request);

        return $response->raw;
    }

    public function cancelOrder($order_id, $reason = "User cancelled")
    {
        $this->request = new CancelOrderRequest();
        $this->applyOptions([
            'orderId' => $order_id,
            'cancelReason'  => $reason,
            'afterNextBill' => true
        ]);
        $this->validate();
        $dispatcher = new Dispatcher();
        $response = $dispatcher->handle($this->request);
    }

    public function getPurchases($customer_id, $options = [])
    {
        $this->request = new QueryOrderRequest();

        // this defaults may be overwritten by the $options
        $this->applyOptions([
            'startDate' => '01/01/2000',
            'endDate'   => '12/31/2100',
            'resultsPerPage'    => 100
        ]);

        $options['customerId'] = $customer_id;
        
        $this->applyOptions($options);
        $this->validate();

        $dispatcher = new Dispatcher();
        $response = $dispatcher->handle($this->request);

        $orders = (new Descendable($response))->get('message.data', []);
        $transactions = new Collection();

        foreach ($orders as $order) {
                $transactions->push([
                    'order_id'          => $order['orderId'],
                    'datetime'          => $order['dateCreated'],
                    'amount'            => $order['price'],
                    'status'            => $order['orderStatus'],
                ]);
        }

        return $transactions;
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

        if (! $customer->billShipSame) {
            $this->request->shipAddress1  = $customer->shipAddress1;
            $this->request->shipCity      = $customer->shipCity;
            $this->request->shipState     = $customer->shipState;
            $this->request->shipPostalCode    = $customer->shipPostalCode;
            $this->request->shipCountry       = $customer->shipCountry;
        } else {
            $this->request->billShipSame = 1;
        }
    }

    protected function applyCreditCard()
    {
        $card = $this->credit_card;
        if (!$card) {
            $this->request->paySource = 'ACCTONFILE';
            return;
        }

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
        if ($product->quantity) {
            $this->request->{$prefix . '_qty'}    = $product->quantity;
        }
        if ($product->price) {
            $this->request->{$prefix . '_price'}  = $product->price;
        }
    }

    protected function applyOptions($options = [])
    {
        $this->options = array_merge($this->options, $options);
        foreach ($this->options as $key => $value) {
            $this->request->$key = $value;
        }
    }

    protected function validate()
    {
        try {
            $this->request->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = "Request: " . $this->request->toJson() .
                "\nErrors: " . $e->validator->errors()->toJson()
            ;
            throw new \Exception($message);
        }
    }

    protected function transformResponse(KonnektiveResponse $konnektive)
    {
        $response = new Response();
        $konnektive = new Descendable($konnektive);
        $response->raw = $konnektive->get('raw');
        $response->message = $konnektive->get('message');
        $response->status = strtolower(trim($konnektive->get('result')));

        $response->customer_id      = $konnektive->get('message.customerId');
        $response->order_id         = $konnektive->get('message.orderId');
        $response->campaign_id      = $konnektive->get('message.campaignId');
        $response->product_id       = $konnektive->get('message.items.0.productId');
        $response->amount           = $konnektive->get('message.amountPaid');

        return $response;
    }
}
