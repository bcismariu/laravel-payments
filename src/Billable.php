<?php

namespace Bcismariu\Laravel\Payments;

use Bcismariu\Laravel\Payments\Processors\Konnektive;
use Bcismariu\Laravel\Payments\Processors\Response;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait Billable 
{
    protected $_payments_card = null;
    protected $_product;
    protected $_options = [];

    public function charge($ammount, $options = [])
    {
        $options['price'] = $ammount;
        $this->_options = $options;
        $this->setProduct($options);

        return $this->processPayment();
    }

    public function order($product_id, $options = [])
    {
        $this->_options = $options;
        $options['product_id'] = $product_id;
        $this->setProduct($options);

        $response = $this->processPayment();

        if ($response->status !== 'success') {
            throw new \Exception(json_encode($response->message));
        }
        
        $order = $this->saveOrder($response);
        return $order;
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
        return $order;
    }

    /**
     * Get all of the subscriptions of the Billable entity
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscriber');
    }

    /**
     * Checks if the Billable entity subscribed to any of the given plans
     * 
     * @param  mixed $plans
     * @return boolean
     */
    public function subscribed($plans = [])
    {
        if (!is_array($plans)) {
            $plans = [$plans];
        }
        $query = $this->subscriptions()->active();
        if (count($plans)) {
            $query->whereIn('plan', $plans);
        }
        return (bool) $query->count();
    }

    /**
     * Subscribes the Billable entity to a plan for the given product_id
     * 
     * @param  string $plan
     * @param  integer $product_id
     * @param  array  $options
     * @return Subscription
     */
    public function subscribe($plan, $product_id, $options = [])
    {
        $order = $this->order($product_id, $options);

        $subscription = new Subscription([
            'plan'          => $plan,
            'product_id'    => $order->product_id,
            'customer_id'   => $order->customer_id,
            'order_id'      => $order->order_id,
            'ends_at'       => Carbon::now()->addMonths(1)->toDateString(),
            'status'        => 'active',
        ]);

        $this->subscriptions()->save($subscription);

        return $subscription;
    }

    /**
     * Unsubscribes the Billable entity from a plan
     * 
     * @param  string $plan
     */
    public function unsubscribe($plan)
    {
        $subscriptions = $this->subscriptions()
            ->wherePlan($plan)
            ->whereNotNull('ends_at')
            ->orderBy('ends_at', 'desc')
            ->get();
        foreach ($subscriptions as $subscription) {
            if (!$subscription->isActive()) {
                continue;
            }
            $processor = $this->getProcessor();
            $processor->cancelOrder($subscription->order_id);
            $subscription->status = 'cancelled';
            $subscription->save();
        }
    }

    /**
     * Gets all purchases of a Billable
     * 
     * @return Illuminate\Support\Collection
     */
    public function purchases()
    {
        $order = $this->orders->first();
        if (!$order) {
            return new Collection();
        }
        $processor = $this->getProcessor();
        return $processor->getPurchases($order->customer_id);
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
        if ($this->_payments_card) {
            $processor->setCreditCard($this->_payments_card);
        }
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