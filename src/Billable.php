<?php

namespace Bcismariu\Laravel\Payments;

use Bcismariu\Laravel\Payments\Processors\Konnektive;
use Bcismariu\Laravel\Payments\Processors\Response;

trait Billable 
{
    protected $_payments_card;
    protected $_product;
    protected $_options;

    public function charge($ammount, $options = [])
    {
        $options['price'] = $ammount;
        $this->_options = $options;
        $this->setProduct($options);

        return $this->processPayment();

    }

    /**
     * This method will retreive the cc info 
     * from the cc repository
     * @return Card
     */
    public function getCreditCard()
    {
        return $this->_payments_card;
    }

    public function setCreditCard(Card $card)
    {
        $this->_payments_card = $card;
    }

    public function saveCreditCard()
    {
        // save to pci compliant api
    }

    public function setProduct($options)
    {
        if (array_key_exists('product_id', $options)) {
            $options['id'] = $options['product_id'];
        }
        $this->_product = new Product($options);
    }

    /**
     * Orders relations management
     */
    public function orders()
    {
        return $this->morphMany(Order::class, 'billable');
    }

    public function saveOrder(Response $response)
    {
        $order = new Order([
            'customer_id'   => $response->customer_id,
            'order_id'      => $response->order_id,
            'campaign_id'   => $response->campaign_id,
            'status'        => $response->status,
            'product_id'    => $response->product_id,
            'amount'        => $response->amount
        ]);

        $this->orders()->save($order);
    }

    /**
     * Transforms the current model to a 
     * recognized Customer object
     * @return Customer
     */
    protected function getTransformedCustomer()
    {
        $transformer = config('payments.transformers.customer');
        return (new $transformer($this))->apply();
    }

    protected function processPayment()
    {
        $processor = $this->getProcessor();

        $processor->setCustomer($this->getTransformedCustomer());
        $processor->setCreditCard($this->_payments_card);
        $processor->addProduct($this->_product);

        return $processor->process();
    }

    /**
     * Returns the configured Payment Processor
     * @return Processor
     */
    protected function getProcessor()
    {
        $processor = config('payments.processor');
        $settings = config("payments.integrations.$processor");

        if (!$settings) {
            throw new \Exception("Processor $processor not found!");
        }

        $settings = array_merge($settings, $this->_options);

        return Processors\Processor::make($settings);
    }
}